<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\Log;

use DataCue\Client;
use DataCue\MagentoModule\Common\Initializer;

class DateList extends \Magento\Backend\App\Action
{
    /**
     * \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    private $resultJsonFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $dateList = [];
        $timestamp = time();
        for ($i = 0; $i < 3; $i++) {
            $date = date('Y-m-d', $timestamp);
            if (file_exists(__DIR__ . "/../../../datacue-$date.log")) {
                $dateList[] = $date;
            }
            $timestamp -= 24 * 3600;
        }

        return $this->resultJsonFactory->create()->setData([
            'status' => 'ok',
            'data' => $dateList,
        ]);
    }
}