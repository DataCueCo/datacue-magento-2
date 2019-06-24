<?php

namespace DataCue\MagentoModule\AdminPage;

/**
 * BaseTemplate
 */
abstract class BaseTemplate extends \Magento\Backend\Block\Template
{
    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        $id = $this->customerSession->getCustomer()->getId();

        if (empty($id)) {
            return null;
        }

        return "$id";
    }

    /**
     * @return bool
     */
    protected function getStaging()
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getApiKey()
    {
        return 'abc';
    }
}
