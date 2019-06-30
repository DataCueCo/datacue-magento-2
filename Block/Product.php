<?php

namespace DataCue\MagentoModule\Block;

use DataCue\MagentoModule\Modules\Product as ProductModule;

/**
 * Product
 */
class Product extends BaseTemplate
{
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
}
