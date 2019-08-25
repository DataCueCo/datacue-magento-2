<?php

namespace DataCue\MagentoModule\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\ObjectManager;

abstract class BaseWidget extends Template
{
    const CSS_DICTIONARY = 'datacue/css/';

    const CSS_FILE_NAME = 'custom.css';

    public function getCustomCssURL()
    {
        $objManager = ObjectManager::getInstance();
        /**
         * @var \Magento\Framework\Filesystem $filesystem
         */
        $filesystem = $objManager->create('Magento\Framework\Filesystem');
        return '/pub/' . $filesystem->getUri(\Magento\Framework\App\Filesystem\DirectoryList::UPLOAD) . '/' . static::CSS_DICTIONARY . static::CSS_FILE_NAME;
    }
}