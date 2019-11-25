<?php

namespace DataCue\MagentoModule\Block;

use DataCue\MagentoModule\Modules\Product as ProductModule;
use Magento\Framework\App\ObjectManager;
use DataCue\MagentoModule\WebsiteOption;

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
            $variantIds = ProductModule::getVariantIds($parentProduct->getId());
            $productUpdate = array_map(function ($id) use ($parentProduct) {
                $variant = ProductModule::getProductById($id);
                return ProductModule::buildVariantForDataCue($parentProduct, $variant, true);
            }, $variantIds);
            return [
                'api_key' => $this->getApiKey(),
                'user_id' => $this->getCustomerId(),
                'options' => ['_staging' => $this->getStaging()],
                'page_type' => 'product',
                'product_id' => $parentProduct->getId(),
                'product_update' => $productUpdate,
            ];
        } else {
            $variantIds = ProductModule::getVariantIds($product->getId());
            if (count($variantIds) > 0) {
                $productUpdate = array_map(function ($id) use ($product) {
                    $variant = ProductModule::getProductById($id);
                    return ProductModule::buildVariantForDataCue($product, $variant, true);
                }, $variantIds);
                return [
                    'api_key' => $this->getApiKey(),
                    'user_id' => $this->getCustomerId(),
                    'options' => ['_staging' => $this->getStaging()],
                    'page_type' => 'product',
                    'product_id' => $product->getId(),
                    'product_update' => $productUpdate,
                ];
            } else {
                return [
                    'api_key' => $this->getApiKey(),
                    'user_id' => $this->getCustomerId(),
                    'options' => ['_staging' => $this->getStaging()],
                    'page_type' => 'product',
                    'product_id' => $product->getId(),
                    'product_update' => [ProductModule::buildProductForDataCue($product, true)],
                ];
            }
        }
    }

    /**
     * @return array
     */
    public function getRecommendationSettings()
    {
        $websiteId = $this->getCurrentWebsiteId();

        $options = WebsiteOption::getOptionsByWebsiteId($websiteId);
        $status = $options['products_status_for_product_page'];
        $type = $options['products_type_for_product_page'];

        return [
            'products_status_for_product_page' => $status ? $status : '0',
            'products_type_for_product_page' => $type ? $type : 'all',
        ];
    }

    public function getCustomCssURL()
    {
        $websiteId = $this->getCurrentWebsiteId();
        $objManager = ObjectManager::getInstance();
        /**
         * @var \Magento\Framework\Filesystem $filesystem
         */
        $filesystem = $objManager->create('Magento\Framework\Filesystem');
        return '/pub/' . $filesystem->getUri(\Magento\Framework\App\Filesystem\DirectoryList::UPLOAD) . '/' . static::CSS_DICTIONARY . "{$websiteId}_" . static::CSS_FILE_NAME;
    }
}
