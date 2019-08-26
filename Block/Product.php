<?php

namespace DataCue\MagentoModule\Block;

use DataCue\MagentoModule\Modules\Product as ProductModule;
use Magento\Framework\App\ObjectManager;

/**
 * Product
 */
class Product extends BaseTemplate
{
    const CSS_DICTIONARY = 'datacue/css/';

    const CSS_FILE_NAME = 'custom.css';
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $collectionFactory, $data);
        $this->_registry = $registry;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }


    /**
     * Get DataCue config
     *
     * @return array
     */
    public function getDataCueConfig()
    {
        $product = $this->getProduct();
        $parentProduct = ProductModule::getParentProduct($product->getId());

        if (!is_null($parentProduct)) {
            return [
                'api_key' => $this->getApiKey(),
                'user_id' => $this->getCustomerId(),
                'options' => ['_staging' => $this->getStaging()],
                'page_type' => 'product',
                'product_id' => $parentProduct->getId(),
                'variant_id' => $product->getId(),
                'product_update' => ProductModule::buildVariantForDataCue($parentProduct, $product),
            ];
        } else {
            $variantIds = ProductModule::getVariantIds($product->getId());
            if (count($variantIds) > 0) {
                $variant = ProductModule::getProductById($variantIds[0]);
                return [
                    'api_key' => $this->getApiKey(),
                    'user_id' => $this->getCustomerId(),
                    'options' => ['_staging' => $this->getStaging()],
                    'page_type' => 'product',
                    'product_id' => $product->getId(),
                    'variant_id' => $variant->getId(),
                    'product_update' => ProductModule::buildVariantForDataCue($product, $variant),
                ];
            } else {
                return [
                    'api_key' => $this->getApiKey(),
                    'user_id' => $this->getCustomerId(),
                    'options' => ['_staging' => $this->getStaging()],
                    'page_type' => 'product',
                    'product_id' => $product->getId(),
                    'variant_id' => 'no-variants',
                    'product_update' => ProductModule::buildProductForDataCue($product),
                ];
            }
        }
    }

    /**
     * @return array
     */
    public function getRecommendationSettings()
    {
        $collection = $this->collectionFactory->create();
        $statusItem = $collection->addFieldToFilter('path', 'datacue/products_status_for_product_page')->getColumnValues('value');
        $status = count($statusItem) > 0 ? $statusItem[0] : '0';
        
        $collection = $this->collectionFactory->create();
        $typeItem = $collection->addFieldToFilter('path', 'datacue/products_type_for_product_page')->getColumnValues('value');
        $type = count($typeItem) > 0 ? $typeItem[0] : 'all';

        return [
            'products_status_for_product_page' => $status,
            'products_type_for_product_page' => $type,
        ];
    }

    public function getCustomCssURL()
    {
        $objManager = ObjectManager::getInstance();
        /**
         * @var \Magento\Framework\Filesystem $filesystem
         */
        $filesystem = $objManager->create('Magento\Framework\Filesystem');
        return '/pub/' . $filesystem->getUri(\Magento\Framework\App\Filesystem\DirectoryList::UPLOAD) . '/' . static::CSS_DICTIONARY . static::CSS_FILE_NAME;
    }
}
