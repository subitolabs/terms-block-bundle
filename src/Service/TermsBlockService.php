<?php

namespace Subitolabs\Bundle\TermsBlockBundle\Service;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class TermsBlockService extends AbstractBlockService
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    protected $pool;

    /**
     * @param string                    $name
     * @param EngineInterface           $templating
     * @param EntityManagerInterface    $entityManager
     */
    public function __construct($name, EngineInterface $templating, EntityManagerInterface $entityManager, $pool)
    {
        parent::__construct($name, $templating);

        $this->entityManager = $entityManager;
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $adminCode = $blockContext->getSetting('admin_code');
        $termLabel = $blockContext->getSetting('term_label');
        $termLink = $blockContext->getSetting('term_link');

        $admin = $this->pool->getAdminByAdminCode($adminCode);

        /** @var EntityRepository $entityRepository */
        $entityRepository = $this->entityManager->getRepository($admin->getClass());

        $groupByField = $blockContext->getSetting('field');

        $query = $entityRepository
            ->createQueryBuilder('i')
            ->groupBy('i.' . $groupByField)
            ->select('count(i.id) as hits, i.' . $groupByField . ' as label')
            ->getQuery()
        ;

        $terms = $query->execute();

        if (!empty($termLink))
        {
            foreach($terms as &$term)
            {
                $term['link'] = call_user_func([$admin, $termLink], $term['label']);
            }
        }

        if (!empty($termLabel))
        {
            foreach($terms as &$term)
            {
                $term['label'] = call_user_func([$admin, $termLabel], $term['label']);
            }
        }

        return $this->renderPrivateResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'terms' => $terms
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'icon' => 'fa-line-chart',
            'text' => 'Statistics',
            'translation_domain' => null,
            'admin_code' => false,
            'filters' => [],
            'limit' => 1000,
            'field' => 'status',
            'term_label' => null,
            'term_link' => null,
            'template' => 'SubitolabsTermsBlockBundle::block.html.twig',
        ]);
    }
}