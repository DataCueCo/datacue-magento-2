<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\Recommendation;

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
        $status = $this->getRequest()->getPostValue("products_status_for_product_page");
        $type = $this->getRequest()->getPostValue("products_type_for_product_page");

        $this->configWriter->save('datacue/products_status_for_product_page', $status);
        $this->configWriter->save('datacue/products_type_for_product_page', $type);
        $this->messageManager->addSuccess('Save recommendations successfully!');

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('datacue_magento/setting/index');
        return $resultRedirect;
    }
}