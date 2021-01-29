# Installation
## Instructions for using composer
Use composer to install this extension. First make sure that Magento is installed via composer, and that there is a valid `composer.json` file present.

Next, install our module using the following command:

    composer require shippop/magento2-ecommerce

Next, install the new module into Magento itself:

    php bin/magento module:enable Shippop_Ecommerce
    php bin/magento setup:upgrade

Done.

### Instructions for manual copy
We recommend `composer` to install this package. However, if you want a manual copy instead, these are the steps:
* Upload the files in the `source/` folder to the folder `app/code/Shippop/Ecommerce` of your site
* Run `php -f bin/magento module:enable Shippop_Ecommerce`
* Run `php -f bin/magento setup:upgrade`
* Flush the Magento cache
* Done
