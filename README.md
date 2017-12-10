## Terms block

Do a ``select count(*), label group by label`` and display a block in Sonata admin dashboard.

## Usage

``composer require subitolabs/terms-block-bundle``

In AppKernel.php

``
public function registerBundles()
{
	$bundles = [
	...
	new \Subitolabs\Bundle\TermsBlockBundle\SubitolabsTermsBlockBundle()
	...
	]
}
``

In sonata.yml

```
sonata_block:
    default_contexts: [admin]
    blocks:
    	...
      subitolabs.admin.block.terms:


sonata_admin:
	...
  dashboard:
      blocks:
          -
            position: left                       
            type:     subitolabs.admin.block.terms
            settings:
                text:  Suggested interest status
                admin_code:  admin.suggested_topic
                field: status
                term_label: formatStatus
                term_link: formatStatusAdminUrl
```