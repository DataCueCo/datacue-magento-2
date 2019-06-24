<?php

namespace DataCue\MagentoModule\Common;

use Magento\Framework\Event\ObserverInterface;

/**
 * Page
 */
class Page implements ObserverInterface
{

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    private $configWriter;

    /**
     * @var \DataCue\Client $client
     */
    private $client;

    public function __construct(
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configWriter = $configWriter;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $schedule = new Schedule($this->collectionFactory, $this->configWriter);
        $schedule->start();
    }
}
