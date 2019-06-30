<?php

namespace DataCue\MagentoModule\Block;

/**
 * Search
 */
class Search extends BaseTemplate
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
            'page_type' => 'search',
            'term' => $this->getRequest()->getParam('q'),
        ];
    }
}
