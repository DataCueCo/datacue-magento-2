<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\Sync;

use DataCue\MagentoModule\Common\Schedule;
use DataCue\MagentoModule\Queue;

class Status extends \Magento\Backend\App\Action
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
     * @var \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    private $configWriter;

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

    public function execute()
    {
        $websiteId = intval($this->getRequest()->getPostValue("website_id"), 10);
        $result = $this->resultJsonFactory->create();

        $schedule = new Schedule($this->collectionFactory, $this->configWriter);
        $schedule->start();

        $res = [
            'categories' => [
                'total' => 0,
                'completed' => 0,
                'failed' => 0,
            ],
            'products' => [
                'total' => 0,
                'completed' => 0,
                'failed' => 0,
            ],
            'users' => [
                'total' => 0,
                'completed' => 0,
                'failed' => 0,
            ],
            'orders' => [
                'total' => 0,
                'completed' => 0,
                'failed' => 0,
            ],
        ];
        $rows = Queue::getAllInitJobByWebsiteId($websiteId);
        foreach($rows as $item) {
            $count = count($item['job']->ids);
            $res[$item['model']]['total'] += $count;
            if (intval($item['status']) === Queue::STATUS_SUCCESS) {
                $res[$item['model']]['completed'] += $count;
            } elseif (intval($item['status']) === Queue::STATUS_FAILURE) {
                $res[$item['model']]['failed'] += $count;
            }
        }

        return $result->setData([
            'status' => 'ok',
            'data' => $res,
        ]);
    }
}