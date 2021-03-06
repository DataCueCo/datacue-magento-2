<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\Setting;

use DataCue\Client;
use DataCue\MagentoModule\Common\Initializer;
use DataCue\MagentoModule\Queue;
use DataCue\MagentoModule\Website;

class Disconnect extends \Magento\Backend\App\Action
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
        $result = $this->resultJsonFactory->create();
        $status = 'ok';
        $websiteId = $this->getRequest()->getPostValue("website_id");
        $credentials = Website::getApiKeyAndApiSecretByWebsiteId($websiteId);

        try {
            $client = new Client(
                $credentials['api_key'],
                $credentials['api_secret'],
                ['max_try_times' => 3],
                file_exists(__DIR__ . '/../../../staging') ? 'development' : 'production'
            );
            $client->client->clear();
            Website::deleteApiKeyAndApiSecretByWebsiteId($websiteId);
            Queue::deleteAllJobsByWebsiteId($websiteId);
        } catch (\Exception $e) {
            $status = 'error';
        }

        return $result->setData(['status' => $status]);
    }
}