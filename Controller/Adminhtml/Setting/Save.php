<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\Setting;

use DataCue\Client;
use DataCue\MagentoModule\Common\Initializer;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\Message\ManagerInterface $messageManager
     */
    protected $messageManager;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);

        $this->configWriter = $configWriter;
        $this->messageManager = $messageManager;
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $apiKey = $this->getRequest()->getPostValue("api_key");
        $apiSecret = $this->getRequest()->getPostValue("api_secret");

        $datacueClient = new Client(
            $apiKey,
            $apiSecret,
            ['max_try_times' => 3],
            file_exists(__DIR__ . '/../../../staging') ? 'development' : 'production'
        );
        $initializer = new Initializer($this->_objectManager->get('Magento\Framework\App\ResourceConnection'), $datacueClient);
        try {
            $initializer->init();
            $this->configWriter->save('datacue/api_key', $apiKey);
            $this->configWriter->save('datacue/api_secret', $apiSecret);
            $this->messageManager->addSuccess('Success!');
        } catch (\DataCue\Exceptions\UnauthorizedException $e) {
            $this->messageManager->addError('Incorrect API key or API secret, please make sure to copy/paste them <strong>exactly</strong> as you see from your dashboard.');
        } catch (\DataCue\Exceptions\Exception $e) {
            $this->messageManager->addError('The synchronization task failed, Please contact the administrator.');
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('datacue_magento/setting/index');
        return $resultRedirect;
    }
}