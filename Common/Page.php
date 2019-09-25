<?php

namespace DataCue\MagentoModule\Common;

use Magento\Framework\Event\ObserverInterface;
use DataCue\Client;
use DataCue\MagentoModule\Utils\Info;

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
        Client::setIntegrationAndVersion('Magento2', Info::getPackageVersion());
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $reSync = new ReSync($this->collectionFactory, $this->configWriter);
        $reSync->execute();
        $schedule = new Schedule($this->collectionFactory, $this->configWriter);
        $schedule->start();
    }
}
