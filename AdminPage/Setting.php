<?php

namespace DataCue\MagentoModule\AdminPage;

use DataCue\MagentoModule\Website;

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
     * @var \Magento\Framework\Filesystem $filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem $filesystem
    )
    {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->filesystem = $filesystem;
    }
}
