<?php

namespace DataCue\MagentoModule\Modules;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use DataCue\MagentoModule\Queue;

class Order implements ObserverInterface
{
    /**
     * @param \Magento\Sales\Model\Order $order
     * @param bool $withId
     * @return array
     */
    public static function buildOrderForDataCue($order, $withId = false)
    {
        $currency = static::getCurrency();

        $item = [
            'user_id' => $order->getCustomerId(),
            'timestamp' => str_replace('+00:00', 'Z', gmdate('c', strtotime($order->getCreatedAt()))),
        ];

        /**
         * @var $orderDetailList \Magento\Sales\Api\Data\OrderItemInterface[]
         */
        $orderDetailList = $order->getAllVisibleItems();
        $item['cart'] = [];
        foreach ($orderDetailList as $orderItem) {
            // if ($orderItem->getProductType() === 'configurable') {
            //     continue;
            // }

            $parentOrderItem = $orderItem->getParentItem();
            if (is_null($parentOrderItem)) {
                $productId = $orderItem->getProductId();
                $parentProductId = Product::getParentProductId($productId);
                $item['cart'][] = [
                    'product_id' => is_null($parentProductId) ? $productId : $parentProductId,
                    'variant_id' => is_null($parentProductId) ? 'no-variants' : $productId,
                    'quantity' => (int)$orderItem->getQtyOrdered(),
                    'unit_price' => (int)$orderItem->getPrice(),
                    'currency' => $currency,
                ];
            } else {
                $item['cart'][] = [
                    'product_id' => $parentOrderItem->getProductId(),
                    'variant_id' => $orderItem->getProductId(),
                    'quantity' => (int)$orderItem->getQtyOrdered(),
                    'unit_price' => (int)$parentOrderItem->getPrice(),
                    'currency' => $currency,
                ];
            }
        }

        if ($withId) {
            $item['order_id'] = $order->getId();
        }

        return $item;
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

    private $isNew = false;

    public function __construct()
    {

    }

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
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $observer->getData('data_object');

        $this->isNew = $order->isObjectNew();
    }

    private function onOrderSaved(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $observer->getData('data_object');

        if ($this->isNew) {
            Queue::addJob(
                'create',
                'orders',
                $order->getId(),
                [
                    'item' => static::buildOrderForDataCue($order, true),
                ]
            );
        } elseif ($order->getStatus() === 'canceled' && !Queue::isJobExisting('cancel', 'orders', $order->getId())) {
            Queue::addJob(
                'cancel',
                'orders',
                $order->getId(),
                [
                    'orderId' => $order->getId(),
                ]
            );
        } elseif ($order->getStatus() !== 'canceled' && Queue::isJobExisting('cancel', 'orders', $order->getId())) {
            Queue::addJob(
                'create',
                'orders',
                $order->getId(),
                [
                    'item' => static::buildOrderForDataCue($order, true),
                ]
            );
        }
    }

    private function onOrderDeleted(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $observer->getData('data_object');

        Queue::addJob(
            'delete',
            'orders',
            $order->getId(),
            [
                'orderId' => $order->getId(),
            ]
        );
    }
}
