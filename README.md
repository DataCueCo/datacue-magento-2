# DataCue for Magento 2 Integration

Learn how to connect DataCue for Magento 2.

## Before You Start

- For the most up-to-date install instructions, read our [DataCue guide for Magento 2](https://help.datacue.co/magento/installation.html).
- This module is does NOT support Magento 1.

## Installation and Setup

Here’s a brief overview of this multi-step process. The installation process **must be completed at the command line.**

- Go to the root directory of Magento 2.
- Run `composer require datacue/magento_module`.
- Run `bin/magento setup:upgrade`.
- Run `bin/magento module:enable --clear-static-content DataCue_MagentoModule`.
- Run `bin/magento setup:upgrade`.
- Run `bin/magento cache:clean`.
- Run `bin/magento setup:di:compile`.
- You can run `bin/magento module:status DataCue_MagentoModule` to make sure the module is enabled.
- You might need to change file permissions or ownership of the generated files after the installation.
- After running the commands above you can login to the store admin. You will find a link called **DataCue Settings** under the **Marketing section**. Click on it.
- Connect the module with your DataCue API Key and Secret (you can find it on your dashboard) and press save.
- Depending on the size of your store the sync process can take a few mins to a few hours.

## Deactivate or Delete the Module

When you deactivate and delete DataCue for Magento 2, we remove all changes made to your store including the Javascript. We also immediately stop syncing any changes to your store data with DataCue.
To deactivate DataCue for Magento 2, follow these steps.

1. Go to the root directory of Magento 2.

2. Run `bin/magento module:disable --clear-static-content DataCue_MagentoModule`.

3. Run `bin/magento module:uninstall DataCue_MagentoModule`.

4. The module has been deleted now. You can run `bin/magento module:status DataCue_MagentoModule` to make sure the module is deleted.