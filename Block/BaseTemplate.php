<?php

namespace DataCue\MagentoModule\Block;

abstract class BaseTemplate extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     */
    private $collectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        $id = $this->customerSession->getCustomer()->getId();

        if (empty($id)) {
            return null;
        }

        return "$id";
    }

    /**
     * @return bool
     */
    protected function getStaging()
    {
        return file_exists(__DIR__ . '/../staging');
    }

    /**
     * @return string
     */
    protected function getApiKey()
    {
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/api_key')->getColumnValues('value');

        return count($items) > 0 ? $items[0] : '';
    }

    abstract public function getDataCueConfig();

    public function getAdditionalEventConfig()
    {
        return null;
    }
}
