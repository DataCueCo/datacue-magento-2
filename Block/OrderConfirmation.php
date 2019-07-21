<?php

namespace DataCue\MagentoModule\Block;

/**
 * Home
 */
class OrderConfirmation extends BaseTemplate
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    )
    {
        parent::__construct($context, $collectionFactory, $data);
        $this->_registry = $registry;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get DataCue config
     *
     * @return array
     */
    public function getDataCueConfig()
    {
        return [
            'api_key' => $this->getApiKey(),
            'user_id' => $this->getCustomerId(),
            'options' => ['_staging' => $this->getStaging()],
            'page_type' => 'order confirmation',
        ];
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        $customerId = parent::getCustomerId();
        if (is_null($customerId)) {
            return $this->getOrder()->getCustomerEmail();
        }

        return $customerId;
    }

    private function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }
}
