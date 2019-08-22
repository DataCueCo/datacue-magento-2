# DataCue for Magento 2 Integration

Learn how to connect DataCue for Magento 2.

## Before You Start

- For the most up-to-date install instructions, read our [DataCue guide for Magento 2](https://help.datacue.co/magento/installation.html).
- This module does NOT support Magento 1.

## Installation and Setup

Here’s a brief overview of this multi-step process. The installation process **must be completed at the command line.**

- Go to the root directory of Magento 2.
- Edit file `composer.json`, change `"minimum-stability": "stable"` to `"minimum-stability": "dev"`.
- Run `composer require datacue/magento_module`.
- Run `bin/magento module:enable --clear-static-content DataCue_MagentoModule`.
- Run `bin/magento setup:upgrade`.
- Run `bin/magento cache:clean`.
- Run `bin/magento setup:di:compile`.
- You can run `bin/magento module:status DataCue_MagentoModule` to make sure the module is enabled.
- You might need to change file permissions or ownership of the generated files after the installation.
- Run `rm -f var/.maintenance.flag`.
- After running the commands above, login to your Magento 2 store's admin. You will find a link called **DataCue Settings** under the **Marketing section**. Click on it.
- Connect the module with your DataCue API Key and Secret (you can find it on your dashboard) and press save.
- Depending on the size of your store the sync process can take a few mins to a few hours.

## Deactivate or Delete the Module

When you deactivate and delete DataCue for Magento 2, we remove all changes made to your store including the Javascript. We also immediately stop syncing any changes to your store data with DataCue.
To deactivate DataCue for Magento 2, follow these steps.

1. Go to the root directory of Magento 2.

2. Run `bin/magento module:disable --clear-static-content DataCue_MagentoModule`.

3. Run `bin/magento module:uninstall --clear-static-content DataCue_MagentoModule`.

4. Run `bin/magento setup:di:compile`.

5. You might need to change file permissions or ownership of the generated files after the uninstallation.

6. The module has been deleted now. You can run `bin/magento module:status DataCue_MagentoModule` to make sure the module is deleted.

## How to add Banners and Products

1. Access the admin panel.

2. Click "CONTENT" in left side bar.

3. Choose "Pages" or "Blocks", then we can access a list page.

4. Select the page or block where you want to insert the banners or products, click on "Edit".

5. In content editor, you can find an icon named "Insert Widget", then click the icon.

6. In widget Type selector, choose "DataCue Banner" or "DataCue Products". If you choose "DataCue Banner", you should fill in "Banner Image" and "Banner Link" in addition. If you choose "DataCue Products", you should fill in "Type" in addition.

7. Click "Insert Widget" and then save the current page or block.

8. Go to the relative page in frontend, you can see the widget you add.

## Update the Module

1. Go to the root directory of Magento 2.

2. Run `composer update datacue/magento_module`.

3. Run `bin/magento setup:upgrade`.

4. You might need to change file permissions or ownership of the generated files after the update.

5. The module has been updated now.
