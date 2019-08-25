<?php

namespace DataCue\MagentoModule\Controller\Adminhtml\CustomCss;

use DataCue\Client;
use DataCue\MagentoModule\Common\Initializer;

class Save extends \Magento\Backend\App\Action
{

    const CSS_DICTIONARY = 'datacue/css/';

    const CSS_FILE_NAME = 'custom.css';

    /**
     * @var \Magento\Framework\Message\ManagerInterface $messageManager
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->filesystem = $filesystem;
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $css = $this->getRequest()->getPostValue("css");
        // var_dump($css);die();
        $uploadDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::UPLOAD);
        $dictionary = $uploadDirectory->getAbsolutePath(static::CSS_DICTIONARY);
        $target = $uploadDirectory->getAbsolutePath(static::CSS_DICTIONARY . static::CSS_FILE_NAME);

        if (!file_exists($dictionary)) {
            mkdir($dictionary, 0777, true);
        }

        file_put_contents($target, $css);

        $this->messageManager->addSuccess('Save custom CSS successfully!');

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('datacue_magento/setting/index');
        return $resultRedirect;
    }
}