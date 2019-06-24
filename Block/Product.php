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
        $this->_registry = $registry;
        $this->customerSession = $customerSession;
        parent::__construct($context, $collectionFactory, $data);
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
        return [
            'api_key' => $this->getApiKey(),
            'user_id' => $this->getCustomerId(),
            'options' => ['_staging' => $this->getStaging()],
            'page_type' => 'product',
            'product_id' => is_null($parentProduct) ? $product->getId() : $parentProduct->getId(),
            'variant_id' => is_null($parentProduct) ? 'no-variants' : $product->getId(),
            'product_update' => is_null($parentProduct) ? ProductModule::buildProductForDataCue($product) : ProductModule::buildVariantForDataCue($parentProduct, $product),
        ];
    }
}
