<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\Setting;

use DataCue\Client;
use DataCue\MagentoModule\Common\Initializer;
use DataCue\MagentoModule\Queue;
use DataCue\MagentoModule\Website;

class SetApiKeyAndApiSecret extends \Magento\Backend\App\Action
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
        $res = [];
        $websiteId = intval($this->getRequest()->getPostValue("website_id"), 10);
        $apiKey = $this->getRequest()->getPostValue("api_key");
        $apiSecret = $this->getRequest()->getPostValue("api_secret");

        $datacueClient = new Client(
            $apiKey,
            $apiSecret,
            ['max_try_times' => 3],
            file_exists(__DIR__ . '/../../../staging') ? 'development' : 'production'
        );
        $initializer = new Initializer($this->_objectManager->get('Magento\Framework\App\ResourceConnection'), $datacueClient, $websiteId);
        try {
            $initializer->check();
            Website::setApiKeyAndApiSecretByWebsiteId($websiteId, $apiKey, $apiSecret);
            $initializer->init();
            $res['status'] = 'ok';
        } catch (\DataCue\Exceptions\UnauthorizedException $e) {
            $res['status'] = 'error';
            $res['msg'] = 'Incorrect API key or API secret, please make sure to copy/paste them exactly as you see from your dashboard.';
        } catch (\DataCue\Exceptions\Exception $e) {
            $res['status'] = 'error';
            $res['msg'] = 'The synchronization task failed, Please contact the administrator.';
        }

        return $this->resultJsonFactory->create()->setData($res);
    }
}