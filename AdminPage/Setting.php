<?php

namespace DataCue\MagentoModule\AdminPage;

/**
 * Setting
 */
class Setting extends BaseTemplate
{

    const CSS_DICTIONARY = 'datacue/css/';

    const CSS_FILE_NAME = 'custom.css';

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem $filesystem
    )
    {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Get api key
     *
     * @return void
     */
    public function getApiKey()
    {
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/api_key')->getColumnValues('value');

        return count($items) > 0 ? $items[0] : '';
    }

    /**
     * Get api secret
     *
     * @return void
     */
    public function getApiSecret()
    {
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/api_secret')->getColumnValues('value');

        return count($items) > 0 ? $items[0] : '';
    }

    /**
     * Get custom CSS
     *
     * @return void
     */
    public function getCustomCss()
    {
        $uploadDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::UPLOAD);
        $target = $uploadDirectory->getAbsolutePath(static::CSS_DICTIONARY . static::CSS_FILE_NAME);

        if (!file_exists($target)) {
            return '';
        }

        return file_get_contents($target);
    }

    /**
     * get recommendation settings
     *
     * @return void
     */
    public function getRecommendationSettings()
    {
        $collection = $this->collectionFactory->create();
        $statusItem = $collection->addFieldToFilter('path', 'datacue/products_status_for_product_page')->getColumnValues('value');
        $status = count($statusItem) > 0 ? $statusItem[0] : '0';

        $collection = $this->collectionFactory->create();
        $typeItem = $collection->addFieldToFilter('path', 'datacue/products_type_for_product_page')->getColumnValues('value');
        $type = count($typeItem) > 0 ? $typeItem[0] : 'all';

        return [
            'products_status_for_product_page' => $status,
            'products_type_for_product_page' => $type,
        ];
    }
}
