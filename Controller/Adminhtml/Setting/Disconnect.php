<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\Setting;

use DataCue\Client;
use DataCue\MagentoModule\Common\Initializer;
use DataCue\MagentoModule\Queue;

class Disconnect extends \Magento\Backend\App\Action
{
    /**
     * \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collectionFactory = $collectionFactory;
        $this->configWriter = $configWriter;
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $status = 'ok';

        try {
            $client = new Client(
                $this->getApiKey(),
                $this->getApiSecret(),
                ['max_try_times' => 3],
                file_exists(__DIR__ . '/../../../staging') ? 'development' : 'production'
            );
            $client->client->clear();
            $this->configWriter->delete('datacue/api_key');
            $this->configWriter->delete('datacue/api_secret');
            Queue::deleteAllJobs();
        } catch (\Exception $e) {
            $status = 'error';
        }

        return $result->setData(['status' => $status]);
    }

    private function getApiKey()
    {
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/api_key')->getColumnValues('value');

        return count($items) > 0 ? $items[0] : '';
    }

    private function getApiSecret()
    {
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/api_secret')->getColumnValues('value');

        return count($items) > 0 ? $items[0] : '';
    }
}