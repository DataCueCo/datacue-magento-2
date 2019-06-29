<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\Log;

use DataCue\Client;
use DataCue\MagentoModule\Common\Initializer;

class View extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);

        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $date = $this->getRequest()->getParam("date");
        $result = $this->resultRawFactory->create();
        $result->setContents(file_get_contents(__DIR__ . "/../../../datacue-$date.log"));
 
        $this->getResponse()->clearHeaders()->setHeader('Content-type','text/plain',true);

        return $result;
    }
}