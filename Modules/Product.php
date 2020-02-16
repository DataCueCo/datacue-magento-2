<?php

namespace DataCue\MagentoModule\Modules;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use DataCue\MagentoModule\Queue;
use DataCue\MagentoModule\Website;
use DataCue\MagentoModule\Utils\Log;
use Exception;

class Product extends Base implements ObserverInterface
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $withId
     * @return array
     */
    public static function buildProductForDataCue($product, $withId = false)
    {
        $item = [
            'name' => $product->getName(),
            'price' => static::getProductPrice($product),
            'full_price' => static::getProductFullPrice($product),
            'link' => $product->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK) . $product->getUrlKey() . '.html',
            'available' => (int)$product->getStatus() === 1,
            'description' => $product->getDescription(),
            'photo_url' => empty($product->getImage()) ? null : $product->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage(),
            'stock' => $product->getExtensionAttributes()->getStockItem() ? $product->getExtensionAttributes()->getStockItem()->getQty() : 0,
            'category_ids' => array_map(function ($categoryId) {
                return "$categoryId";
            }, $product->getCategoryIds()),
            'brand' => null,
        ];

        if ($withId) {
            $item['product_id'] = '' . $product->getId();
            $item['variant_id'] = 'no-variants';
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
        $item = [
            'name' => $product->getName(),
            'price' => static::getProductPrice($variant),
            'full_price' => static::getProductFullPrice($variant),
            'link' => $variant->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK) . $product->getUrlKey() . '.html',
            'available' => (int)$product->getStatus() === 1,
            'description' => $product->getDescription(),
            'photo_url' => empty($product->getImage()) ? null : $product->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage(),
            'stock' => $variant->getExtensionAttributes()->getStockItem() ? $variant->getExtensionAttributes()->getStockItem()->getQty() : 0,
            'category_ids' => array_map(function ($categoryId) {
                return "$categoryId";
            }, $product->getCategoryIds()),
            'brand' => null,
        ];

        if ($withId) {
            $item['product_id'] = '' . $product->getId();
            $item['variant_id'] = '' . $variant->getId();
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

    /**
     * Get variant ids by product id
     *
     * @param int $productId
     * @return int[]
     */
    public static function getVariantIds($productId)
    {
        $objectManager = ObjectManager::getInstance();
        $variantIds = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getChildrenIds($productId);
        return array_keys($variantIds[0]);
    }

    public static function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->create('Magento\Catalog\Helper\Data')->getTaxPrice($product, $product->getFinalPrice(), true);
    }

    public static function getProductFullPrice(\Magento\Catalog\Model\Product $product)
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->create('Magento\Catalog\Helper\Data')->getTaxPrice($product, $product->getPrice(), true);
    }

    /**
     * @var \Magento\Catalog\Model\Product $productBeforeSave
     */
    private $productBeforeSave;

    /**
     * @var int[] $variantIdsBeforeSave
     */
    private $variantIdsBeforeSave;

    /**
     * @var int[] $websiteIdsBeforeSave
     */
    private $websiteIdsBeforeSave;

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            switch ($observer->getEvent()->getName()) {
                case 'catalog_product_save_before':
                    $this->beforeModelProductSaved($observer);
                    break;
                case 'catalog_product_save_after':
                    $this->onModelProductSaved($observer);
                    break;
                case 'catalog_product_delete_before':
                    $this->onModelProductDeleted($observer);
                    break;
                case 'checkout_cart_save_after':
                    $this->onCartSaved($observer);
                default:
                    break;
            }
        } catch (Exception $e) {

        }
    }

    private function beforeModelProductSaved(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Catalog\Model\Product $product
         */
        if ($observer->getData('data_object')->getTypeId() !== 'virtual') {
            Log::info('beforeModelProductSaved');
            $product = $observer->getData('data_object');
            $this->productBeforeSave = static::getProductById($product->getId());
            $this->variantIdsBeforeSave = static::getVariantIds($this->productBeforeSave->getId());
            $this->websiteIdsBeforeSave = $this->productBeforeSave->getWebsiteIds();
            Log::info($this->websiteIdsBeforeSave);
        }
    }

    private function onModelProductSaved(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Catalog\Model\Product $product
         */
        $product = $observer->getData('data_object');

        Log::info('onModelProductSaved');
            
        if ($product->isObjectNew()) {
            $product = static::getProductById($product->getId());
            $websiteIds = $product->getWebsiteIds();
            if ($product->getTypeId() === 'simple') {
                foreach ($websiteIds as $websiteId) {
                    Queue::addJob(
                        'create',
                        'products',
                        $product->getId(),
                        [
                            'productId' => $product->getId(),
                            'variantId' => 'no-variants',
                            'item' => static::buildProductForDataCue($product, true),
                        ],
                        $websiteId
                    );
                }
            } elseif ($product->getTypeId() === 'configurable') {
                $variantIds = static::getVariantIds($product->getId());
                foreach ($websiteIds as $websiteId) {
                    foreach ($variantIds as $variantId) {
                        $variant = static::getProductById($variantId);
                        Queue::addJob(
                            'create',
                            'variants',
                            $variant->getId(),
                            [
                                'productId' => $product->getId(),
                                'variantId' => $variant->getId(),
                                'item' => static::buildVariantForDataCue($product, $variant, true),
                            ],
                            $websiteId
                        );
                    }
                }
            }
        } else {
            if ($product->getTypeId() !== 'virtual') {
                foreach ($this->websiteIdsBeforeSave as $websiteId) {
                    if (count($this->variantIdsBeforeSave) > 0) {
                        foreach ($this->variantIdsBeforeSave as $variantId) {
                            Queue::addJob(
                                'delete',
                                'variants',
                                $variantId,
                                [
                                    'productId' => $product->getId(),
                                    'variantId' => $variantId,
                                ],
                                $websiteId
                            );
                        }
                    } else {
                        Queue::addJob(
                            'delete',
                            'products',
                            $product->getId(),
                            [
                                'productId' => $product->getId(),
                                'variantId' => 'no-variants',
                            ],
                            $websiteId
                        );
                    }
                }
            }

            $websiteIds = $product->getWebsiteIds();
            if ($product->getTypeId() === 'simple') {
                foreach ($websiteIds as $websiteId) {
                    Queue::addJob(
                        'update',
                        'products',
                        $product->getId(),
                        [
                            'productId' => $product->getId(),
                            'variantId' => 'no-variants',
                            'item' => static::buildProductForDataCue($product, false),
                        ],
                        $websiteId
                    );
                }
            } elseif ($product->getTypeId() === 'configurable') {
                // handle variants
                $variantIdsAfterSaved = static::getVariantIds($product->getId());

                foreach ($websiteIds as $websiteId) {
                    foreach ($variantIdsAfterSaved as $variantId) {
                        $variant = static::getProductById($variantId);
                        Queue::addJob(
                            'update',
                            'variants',
                            $variant->getId(),
                            [
                                'productId' => $product->getId(),
                                'variantId' => $variant->getId(),
                                'item' => static::buildVariantForDataCue($product, $variant, false),
                            ],
                            $websiteId
                        );
                    }
                }
            } elseif ($product->getTypeId() === 'virtual') {
                $parentProduct = static::getParentProduct($product->getId());
                foreach ($websiteIds as $websiteId) {
                    if (is_null($parentProduct)) {
                        Queue::addJob(
                            'update',
                            'products',
                            $product->getId(),
                            [
                                'productId' => $product->getId(),
                                'variantId' => 'no-variants',
                                'item' => static::buildProductForDataCue($product, false),
                            ],
                            $websiteId
                        );
                    } else {
                        Queue::addJob(
                            'update',
                            'variants',
                            $product->getId(),
                            [
                                'productId' => $parentProduct->getId(),
                                'variantId' => $product->getId(),
                                'item' => static::buildVariantForDataCue($parentProduct, $product, false),
                            ],
                            $websiteId
                        );
                    }
                }
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
        $websiteIds = $product->getWebsiteIds();

        foreach ($websiteIds as $websiteId) {
            if (is_null($parentProductId)) {
                $variantIds = static::getVariantIds($product->getId());
    
                if (count($variantIds) === 0) {
                    Queue::addJob(
                        'delete',
                        'products',
                        $product->getId(),
                        [
                            'productId' => $product->getId(),
                            'variantId' => 'no-variants',
                        ],
                        $websiteId
                    );
                } else {
                    
                    foreach ($variantIds as $variantId) {
                        Queue::addJob(
                            'delete',
                            'variants',
                            $variantId,
                            [
                                'productId' => $product->getId(),
                                'variantId' => $variantId,
                            ],
                            $websiteId
                        );
                        $variant = static::getProductById($variantId);
                        Queue::addJob(
                            'update',
                            'products',
                            $variant->getId(),
                            [
                                'productId' => $variant->getId(),
                                'variantId' => 'no-variants',
                                'item' => static::buildVariantForDataCue($product, $variant, false),
                            ],
                            $websiteId
                        );
                    }
                }
            } else {
                Queue::addJob(
                    'delete',
                    'variants',
                    $product->getId(),
                    [
                        'productId' => $parentProductId,
                        'variantId' => $product->getId(),
                    ],
                    $websiteId
                );
            }
        }
    }

    private function onCartSaved(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Checkout\Model\Cart $cart
         */
        $cart = $observer->getData('cart');
        $res = [];

        /**
         * @var \Magento\Quote\Model\Quote\Item[] $cartItems
         */
        $cartItems = $cart->getQuote()->getAllItems();

        foreach($cartItems as $cartItem) {
            if ($cartItem->getProductType() === 'configurable') {
                continue;
            }
            $storeId = $cartItem->getStoreId();

            $parentCartItem = $cartItem->getParentItem();
            $productId = $cartItem->getProduct()->getId();
            $parentProductId = \DataCue\MagentoModule\Modules\Product::getParentProductId($productId);

            $item = [
                'product_id' => is_null($parentProductId) ? $productId : $parentProductId,
                'variant_id' => is_null($parentProductId) ? 'no-variants' : $productId,
                'quantity' => is_null($parentCartItem) ? $cartItem->getQty() : $parentCartItem->getQty(),
                'currency' => \DataCue\MagentoModule\Modules\Order::getCurrency(),
                'unit_price' => static::getProductPrice(static::getProductById($productId)),
            ];

            if (!array_key_exists($storeId, $res)) {
                $res[$storeId] = [];
            }
            $res[$storeId][] = $item;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $userId = $customerSession->getCustomer()->getId();

        foreach ($res as $storeId => $storeRes) {
            /**
             * @var \Magento\Store\Model\StoreManagerInterface $storeManager
             */
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $store = $storeManager->getStore($storeId);
            $baseURL = $store->getBaseUrl();
            $websiteId = $store->getWebsiteId();

            Queue::addJobWithoutModelId(
                'track',
                'events',
                [
                    'user' => [
                        'user_id' => empty($userId) ? null : "$userId",
                    ],
                    'event' => [
                        'type' => 'cart',
                        'subtype' => 'update',
                        'cart' => $storeRes,
                        'cart_link' => "{$baseURL}checkout/cart/",
                    ]
                ],
                $websiteId
            );
        }
    }
}
