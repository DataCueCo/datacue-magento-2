<?php

namespace DataCue\MagentoModule\AdminPage;

/**
 * Setting
 */
class Setting extends BaseTemplate
{

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
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
    )
    {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get api key
     *
     * @return void
     */
    public function getApiKey()
    {
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/api_key')->getColumnValues('value');

        return count($items) > 0 ? $items[0] : '';
    }

    /**
     * Get api secret
     *
     * @return void
     */
    public function getApiSecret()
    {
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/api_secret')->getColumnValues('value');

        return count($items) > 0 ? $items[0] : '';
    }
}
