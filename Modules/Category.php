<?php

namespace DataCue\MagentoModule\Modules;

use DataCue\MagentoModule\Queue;
use DataCue\MagentoModule\Website;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class Category extends Base implements ObserverInterface
{
    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param bool $withId
     * @return array
     */
    public static function buildCategoryForDataCue($category, $withId = false)
    {
        $item = [
            'name' => $category->getName(),
            'link' => static::getCategoryLink($category),
        ];

        if ($withId) {
            $item['category_id'] = $category->getId();
        }

        return $item;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public static function getCategoryLink($category)
    {
        $baseUrl = $category->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $categoryUrlKey = [];
        while ($category) {
            $categoryUrlKey[] = $category->hasData('url_key')
                ? strtolower($category->getUrlKey())
                : trim(strtolower(preg_replace('#[^0-9a-z%]+#i', '-', $category->getName())), '-');

            $category = $category->getParentCategory();
            if (1 == $category->getParentId()) {
                $category = null;
            }
        }

        return $baseUrl . implode('/', array_reverse($categoryUrlKey)) . '.html';
    }

    /**
     * @param $id
     * @return \Magento\Catalog\Model\Category
     */
    public static function getCategoryById($id)
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->create('\Magento\Catalog\Model\Category')->load($id);
    }

    private $isNew = false;

    public function execute(Observer $observer)
    {
        switch ($observer->getEvent()->getName()) {
            case 'catalog_category_save_before':
                $this->setIsNewTag($observer);
                break;
            case 'catalog_category_save_after':
                $this->onCategorySaved($observer);
                break;
            case 'catalog_category_delete_before':
                $this->onCategoryDeleted($observer);
                break;
            default:
                break;
        }
    }

    private function setIsNewTag(Observer $observer)
    {
        /**
         * @var \Magento\Catalog\Model\Category $category
         */
        $category = $observer->getData('data_object');

        $this->isNew = $category->isObjectNew();
    }

    private function onCategorySaved(Observer $observer)
    {
        /**
         * @var \Magento\Catalog\Model\Category $category
         */
        $category = $observer->getData('data_object');
        if (1 == $category->getParentId()) {
            return;
        }

        $websiteIds = Website::getActiveWebsiteIds();
        if ($this->isNew) {
            foreach ($websiteIds as $websiteId) {
                Queue::addJob(
                    'create',
                    'categories',
                    $category->getId(),
                    [
                        'item' => static::buildCategoryForDataCue($category, true),
                    ],
                    $websiteId
                );
            }
        } else {
            foreach ($websiteIds as $websiteId) {
                Queue::addJob(
                    'update',
                    'categories',
                    $category->getId(),
                    [
                        'categoryId' => $category->getId(),
                        'item' => static::buildCategoryForDataCue($category, false),
                    ],
                    $websiteId
                );
            }
        }
    }

    private function onCategoryDeleted(Observer $observer)
    {
        /**
         * @var \Magento\Catalog\Model\Category $category
         */
        $category = $observer->getData('data_object');
        if (1 == $category->getParentId()) {
            return;
        }

        $websiteIds = Website::getActiveWebsiteIds();
        foreach ($websiteIds as $websiteId) {
            Queue::addJob(
                'delete',
                'categories',
                $category->getId(),
                [
                    'categoryId' => $category->getId(),
                ],
                $websiteId
            );
        }
    }
}