<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\Recommendation;

use DataCue\Client;
use DataCue\MagentoModule\Common\Initializer;
use DataCue\MagentoModule\WebsiteOption;

class Save extends \Magento\Backend\App\Action
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
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem $filesystem
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
        $products_status_for_product_page = $this->getRequest()->getPostValue("products_status_for_product_page");
        $products_type_for_product_page = $this->getRequest()->getPostValue("products_type_for_product_page");
        WebsiteOption::setOption($websiteId, 'products_status_for_product_page', $products_status_for_product_page);
        WebsiteOption::setOption($websiteId, 'products_type_for_product_page', $products_type_for_product_page);

        return $this->resultJsonFactory->create()->setData([
            'status' => 'ok',
        ]);
    }
}