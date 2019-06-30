<?php

namespace DataCue\MagentoModule\Block;

/**
 * Category
 */
class Category extends BaseTemplate
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
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        return $this->_registry->registry('current_category');
    }


    /**
     * Get DataCue config
     *
     * @return array
     */
    public function getDataCueConfig()
    {
        return [
            'api_key' => $this->getApiKey(),
            'user_id' => $this->getCustomerId(),
            'options' => ['_staging' => $this->getStaging()],
            'page_type' => 'category',
            'category_name' => $this->getCategory()->getName(),
        ];
    }
}
