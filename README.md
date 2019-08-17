# Techfactory-Recommendations integration with Magento 2

Requirements:
* Magento 2

### Instructions for manual installation

* Move contents of "Techfactory" folder of the module provided to "<magento_root_dir>/app/code/Techfactory/Recommendations"
* Run `bin/magento module:enable Techfactory_Recommendations`
* Run `bin/magento setup:upgrade`
* Run `bin/magento setup:di:compile`
* Run `bin/magento cache:disable full_page block_html`
* Run `bin/magento cache:clean`
* Run `bin/magento cache:flush`
