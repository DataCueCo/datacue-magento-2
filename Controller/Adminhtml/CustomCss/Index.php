<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\CustomCss;

use DataCue\Client;
use DataCue\MagentoModule\Common\Initializer;

class Index extends \Magento\Backend\App\Action
{

    const CSS_DICTIONARY = 'datacue/css/';

    const CSS_FILE_NAME = 'custom.css';

    /**
     * \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    protected $filesystem;

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
        $this->filesystem = $filesystem;
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $websiteId = $this->getRequest()->getPostValue("website_id");
        $uploadDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::UPLOAD);
        $target = $uploadDirectory->getAbsolutePath(static::CSS_DICTIONARY . "{$websiteId}_" . static::CSS_FILE_NAME);

        return $this->resultJsonFactory->create()->setData([
            'status' => 'ok',
            'data' => [
                'content' => file_exists($target) ? file_get_contents($target) : '',
            ],
        ]);
    }
}