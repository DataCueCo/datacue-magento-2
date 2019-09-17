<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\Setting;

use DataCue\Client;
use DataCue\MagentoModule\Common\Initializer;
use DataCue\MagentoModule\Queue;
use DataCue\MagentoModule\Website;

class GetApiKeyAndApiSecret extends \Magento\Backend\App\Action
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
        $websiteId = $this->getRequest()->getPostValue("website_id");
        $result = $this->resultJsonFactory->create();

        return $result->setData([
            'status' => 'ok',
            'data' => Website::getApiKeyAndApiSecretByWebsiteId($websiteId),
        ]);
    }
}