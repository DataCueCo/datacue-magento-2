<?php

namespace DataCue\MagentoModule\Modules;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use DataCue\MagentoModule\Queue;

class Product implements ObserverInterface
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $withId
     * @return array
     */
    public static function buildProductForDataCue($product, $withId = false)
    {
        $objManager = ObjectManager::getInstance();
        $item = [
            'name' => $product->getName(),
            'price' => (float)$product->getSpecialPrice() === (float)0 ? (float)$product->getPrice() : (float)$product->getSpecialPrice(),
            'full_price' => (float)$product->getPrice(),
            'link' => $product->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK) . $product->getUrlKey() . '.html',
            'available' => (int)$product->getStatus() === 1,
            'description' => $product->getDescription(),
            'photo_url' => $product->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage(),
            'stock' => $product->getExtensionAttributes()->getStockItem() ? $product->getExtensionAttributes()->getStockItem()->getQty() : 0,
            'categories' => array_map(function ($categoryId) use ($objManager) {
                /**
                 * @var $category \Magento\Catalog\Model\Category
                 */
                $category = $objManager->create('Magento\Catalog\Model\Category')->load($categoryId);
                return $category->getName();
            }, $product->getCategoryIds()),
            'brand' => null,
        ];

        if (count($item['categories']) > 0) {
            $item['main_category'] = $item['categories'][0];
        } else {
            $item['main_category'] = null;
        }

        if ($withId) {
            $item['product_id'] = $product->getId();
            $item['variant_id'] = 'no_variants';
        }

        return $item;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $variant
     * @param bool $withId
     * @return array
     */
    public static function buildVariantForDataCue($product, $variant, $withId = false)
    {
        $objManager = ObjectManager::getInstance();
        $item = [
            'name' => $product->getName(),
            'price' => (float)$variant->getSpecialPrice() === (float)0 ? (float)$variant->getPrice() : (float)$variant->getSpecialPrice(),
            'full_price' => (float)$variant->getPrice(),
            'link' => $variant->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK) . $variant->getUrlKey() . '.html',
            'available' => (int)$product->getStatus() === 1,
            'description' => $product->getDescription(),
            'photo_url' => $product->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage(),
            'stock' => $variant->getExtensionAttributes()->getStockItem() ? $variant->getExtensionAttributes()->getStockItem()->getQty() : 0,
            'categories' => array_map(function ($categoryId) use ($objManager) {
                /**
                 * @var $category \Magento\Catalog\Model\Category
                 */
                $category = $objManager->create('Magento\Catalog\Model\Category')->load($categoryId);
                return $category->getName();
            }, $product->getCategoryIds()),
            'brand' => null,
        ];

        if (count($item['categories']) > 0) {
            $item['main_category'] = $item['categories'][0];
        } else {
            $item['main_category'] = null;
        }

        if ($withId) {
            $item['product_id'] = $product->getId();
            $item['variant_id'] = $variant->getId();
        }

        return $item;
    }

    /**
     * @param int $childId
     * @return null|\Magento\Catalog\Model\Product
     */
    public static function getParentProduct($childId)
    {
        $objectManager = ObjectManager::getInstance();
        $parentId = static::getParentProductId($childId);
        if (is_null($parentId)) {
            return null;
        }

        return $objectManager->create('Magento\Catalog\Model\Product')->load($parentId);
    }

    /**
     * @param int $childId
     * @return null|int
     */
    public static function getParentProductId($childId)
    {
        $objectManager = ObjectManager::getInstance();
        $products = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($childId);
        if(isset($products[0])){
            return $products[0];
        }

        return null;
    }

    /**
     * @param int $id
     * @return null|\Magento\Catalog\Model\Product
     */
    public static function getProductById($id)
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->create('Magento\Catalog\Model\Product')->load($id);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product[]
     */
    public static function getVariants(\Magento\Catalog\Model\Product $product)
    {
        $instance = $product->getTypeInstance();
        if (method_exists($instance, 'getUsedProducts')) {
            return $instance->getUsedProducts($product);
        }

        return [];
    }

    public function __construct()
    {

    }



    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        switch ($observer->getEvent()->getName()) {
            case 'controller_action_catalog_product_save_entity_after':
                $this->onActionProductSaved($observer);
                break;
            case 'catalog_product_save_after':
                $this->onModelProductSaved($observer);
                break;
            case 'catalog_product_delete_before':
                $this->onModelProductDeleted($observer);
                break;
            default:
                break;
        }

    }

    private function onActionProductSaved(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $observer->getData('product');

//        var_dump($product->getTypeId()); die();
//
//        $parentProduct = static::getParentProduct($product);

        if ($product->isObjectNew()) {
            if ($product->getTypeId() === 'configurable' || $product->getTypeId() === 'simple') {
                Queue::addJob(
                    'create',
                    'products',
                    $product->getId(),
                    [
                        'productId' => $product->getId(),
                        'variantId' => 'no-variants',
                        'item' => static::buildProductForDataCue($product, true),
                    ]
                );

                if ($product->getTypeId() === 'configurable') {
                    $variants = static::getVariants($product);
                    foreach ($variants as $variant) {
                        Queue::addJob(
                            'create',
                            'variants',
                            $variant->getId(),
                            [
                                'productId' => $product->getId(),
                                'variantId' => $variant->getId(),
                                'item' => static::buildVariantForDataCue($product, $variant, true),
                            ]
                        );
                    }
                }
            }
        }
    }

    private function onModelProductSaved(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $observer->getData('data_object');

        $parentProduct = static::getParentProduct($product->getId());

        if (is_null($parentProduct)) { // product with 'no-variants'
            if (!$product->isObjectNew()) {
                Queue::addJob(
                    'update',
                    'products',
                    $product->getId(),
                    [
                        'productId' => $product->getId(),
                        'variantId' => 'no-variants',
                        'item' => static::buildProductForDataCue($product, false),
                    ]
                );
            }
        } else {
            if (!$product->isObjectNew()) {
                Queue::addJob(
                    'update',
                    'variants',
                    $product->getId(),
                    [
                        'productId' => $parentProduct->getId(),
                        'variantId' => $product->getId(),
                        'item' => static::buildVariantForDataCue($parentProduct, $product, false),
                    ]
                );
            }
        }
    }

    private function onModelProductDeleted(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $observer->getData('data_object');

        $parentProductId = static::getParentProductId($product->getId());

        if (is_null($parentProductId)) {
            Queue::addJob(
                'delete',
                'products',
                $product->getId(),
                [
                    'productId' => $product->getId(),
                    'variantId' => 'no-variants',
                ]
            );
        } else {
            Queue::addJob(
                'delete',
                'variants',
                $product->getId(),
                [
                    'productId' => $parentProductId,
                    'variantId' => $product->getId(),
                ]
            );
        }
    }
}
