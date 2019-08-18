<?php

namespace DataCue\MagentoModule\Block;

/**
 * Checkout
 */
class Checkout extends BaseTemplate
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $collectionFactory, $data);
        $this->_registry = $registry;
        $this->customerSession = $customerSession;
        $this->cart = $cart;
    }

    public function getCartItems()
    {
        $res = [];

        /** @var \Magento\Quote\Model\Quote\Item[] $cartItems */
        $cartItems = $this->cart->getQuote()->getAllItems();

        foreach($cartItems as $cartItem) {
            if ($cartItem->getProductType() === 'configurable') {
                continue;
            }

            $parentCartItem = $cartItem->getParentItem();
            $productId = $cartItem->getProduct()->getId();
            $parentProductId = \DataCue\MagentoModule\Modules\Product::getParentProductId($productId);

            $item = [
                'product_id' => is_null($parentProductId) ? $productId : $parentProductId,
                'variant_id' => is_null($parentProductId) ? 'no-variants' : $productId,
                'quantity' => is_null($parentCartItem) ? $cartItem->getQty() : $parentCartItem->getQty(),
                'currency' => \DataCue\MagentoModule\Modules\Order::getCurrency(),
                'unit_price' => static::getProductPrice(static::getProductById($productId)),
            ];

            $res[] = $item;
        }

        return $res;
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
            'page_type' => 'checkout',
        ];
    }

    /**
     * Get DataCue config
     *
     * @return array
     */
    public function getAdditionalEventConfig()
    {
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');

        return [
            'type' => 'checkout',
            'subtype' => 'started',
            'cart' => $this->getCartItems(),
            'cart_link' => $urlInterface->getCurrentUrl(),
        ];
    }
}
