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
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        parent::__construct($context);

        $this->configWriter = $configWriter;
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $apiKey = $this->getRequest()->getPostValue("api_key");
        $this->configWriter->save('datacue/api_key', $apiKey);
        $apiSecret = $this->getRequest()->getPostValue("api_secret");
        $this->configWriter->save('datacue/api_secret', $apiSecret);

        $datacueClient = new Client(
            $apiKey,
            $apiSecret,
            ['max_try_times' => 3],
            file_exists(__DIR__ . '/../../../staging') ? 'development' : 'production'
        );

        $redirectParams = [];
        $initializer = new Initializer($this->_objectManager->get('Magento\Framework\App\ResourceConnection'), $datacueClient);
        try {
            $initializer->init();
            $redirectParams['status'] = 'success';
        } catch (\DataCue\Exceptions\UnauthorizedException $e) {
            $redirectParams['status'] = 'authorized_error';
        } catch (\DataCue\Exceptions\Exception $e) {
            $redirectParams['status'] = 'sync_fail';
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('datacue_magento/setting/index', $redirectParams);
        return $resultRedirect;
    }
}