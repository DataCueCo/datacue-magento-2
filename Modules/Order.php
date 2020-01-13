<?php

namespace DataCue\MagentoModule\Modules;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use DataCue\MagentoModule\Queue;

class Order extends Base implements ObserverInterface
{
    /**
     * @param \Magento\Sales\Model\Order $order
     * @param bool $withId
     * @return array
     */
    public static function buildOrderForDataCue($order, $withId = false)
    {
        $currency = static::getCurrency();
        $customerId = $order->getCustomerId();

        $item = [
            'user_id' => empty($customerId) ? $order->getCustomerEmail() : $customerId,
            'timestamp' => str_replace('+00:00', 'Z', gmdate('c', strtotime($order->getCreatedAt()))),
            'order_status' => $order->getStatus() === 'canceled' ? 'cancelled' : 'completed',
        ];

        /**
         * @var \Magento\Sales\Api\Data\OrderItemInterface[] $orderDetailList
         */
        $orderDetailList = $order->getAllItems();
        $item['cart'] = [];
        foreach ($orderDetailList as $orderItem) {
            if ($orderItem->getProductType() === 'configurable') {
                continue;
            }

            $productId = $orderItem->getProductId();
            $parentProductId = Product::getParentProductId($productId);
            $item['cart'][] = [
                'product_id' => empty($parentProductId) ? $productId : $parentProductId,
                'variant_id' => empty($parentProductId) ? 'no-variants' : $productId,
                'quantity' => (int)$orderItem->getQtyOrdered(),
                'unit_price' => Product::getProductPrice(Product::getProductById($productId)),
                'currency' => $currency,
            ];
        }

        if ($withId) {
            $item['order_id'] = $order->getId();
        }

        return $item;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public static function buildGuestUserForDataCue($order)
    {
        return [
            'user_id' => $order->getCustomerEmail(),
            'email' => $order->getCustomerEmail(),
            'title' => null,
            'first_name' => $order->getShippingAddress()->getFirstname(),
            'last_name' => $order->getShippingAddress()->getLastname(),
            'email_subscriber' => false,
            'guest_account' => true,
        ];
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public static function isOrderValid($order)
    {
        if (empty($order->getCustomerId())) {
            return false;
        }

        if (empty($order->getCustomerEmail())) {
            return false;
        }

        $orderDetailList = $order->getAllItems();
        foreach ($orderDetailList as $orderItem) {
            if ($orderItem->getProductType() === 'configurable') {
                continue;
            }

            $productId = $orderItem->getProductId();
            if (!empty($productId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $id
     * @return null|\Magento\Sales\Model\Order
     */
    public static function getOrderById($id)
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->create('Magento\Sales\Model\Order')->load($id);
    }

    public static function getCurrency()
    {
        $objectManager = ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $store = $storeManager->getStore();
        return $store->getWebsite()->getBaseCurrency()->getCode();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public static function getWebsiteId($order)
    {
        $store = $order->getStore();

        return $store->getWebsiteId();
    }

    private $isNew = false;

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        switch ($observer->getEvent()->getName()) {
            case 'sales_order_save_before':
                $this->setIsNewTag($observer);
                break;
            case 'sales_order_save_after':
                $this->onOrderSaved($observer);
                break;
            case 'sales_order_delete_after':
                $this->onOrderDeleted($observer);
                break;
            default:
                break;
        }
    }

    private function setIsNewTag(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $observer->getData('data_object');

        $this->isNew = $order->isObjectNew();
    }

    private function onOrderSaved(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $observer->getData('data_object');
        $websiteId = static::getWebsiteId($order);

        if ($this->isNew) {
            if (static::isOrderValid($order)) {
                if (is_null($order->getCustomerId())) {
                    Queue::addJob(
                        'create',
                        'guest_users',
                        $order->getId(),
                        [
                            'item' => static::buildGuestUserForDataCue($order),
                        ],
                        $websiteId
                    );
                }
                Queue::addJob(
                    'create',
                    'orders',
                    $order->getId(),
                    [
                        'item' => static::buildOrderForDataCue($order, true),
                    ],
                    $websiteId
                );
            }
        } elseif ($order->getStatus() === 'canceled' && !Queue::isJobExisting('cancel', 'orders', $order->getId())) {
            Queue::addJob(
                'cancel',
                'orders',
                $order->getId(),
                [
                    'orderId' => $order->getId(),
                ],
                $websiteId
            );
        } elseif ($order->getStatus() !== 'canceled' && Queue::isJobExisting('cancel', 'orders', $order->getId())) {
            if (static::isOrderValid($order)) {
                Queue::addJob(
                    'create',
                    'orders',
                    $order->getId(),
                    [
                        'item' => static::buildOrderForDataCue($order, true),
                    ],
                    $websiteId
                );
            }
        }
    }

    private function onOrderDeleted(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $observer->getData('data_object');
        $websiteId = static::getWebsiteId($order);

        Queue::addJob(
            'delete',
            'orders',
            $order->getId(),
            [
                'orderId' => $order->getId(),
            ],
            $websiteId
        );
    }
}
