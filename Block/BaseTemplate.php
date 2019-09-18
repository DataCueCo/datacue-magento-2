<?php

namespace DataCue\MagentoModule\Block;

use DataCue\MagentoModule\Utils\Log;
use DataCue\MagentoModule\Website;
use Magento\Framework\App\ObjectManager;

abstract class BaseTemplate extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     */
    protected $collectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        array $data = []
    ) {
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
        $res = Website::getApiKeyAndApiSecretByWebsiteId($this->getCurrentWebsiteId());

        return !empty($res) ? $res['api_key'] : '';
    }

    abstract public function getDataCueConfig();

    public function getAdditionalEventConfig()
    {
        return null;
    }

    protected function getCurrentWebsiteId()
    {
        $objectManager = ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $store = $storeManager->getStore();
        return $store->getWebsiteId();
    }
}
