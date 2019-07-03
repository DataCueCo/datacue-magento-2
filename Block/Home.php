<?php

namespace DataCue\MagentoModule\Block;

/**
 * Home
 */
class Home extends BaseTemplate
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->collectionFactory = $objectManager->create('Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory');
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/api_key')->getColumnValues('value');

        return [
            'api_key' => $this->getApiKey(),
            'user_id' => $this->getCustomerId(),
            'options' => ['_staging' => $this->getStaging()],
            'page_type' => 'home',
        ];
    }
}
