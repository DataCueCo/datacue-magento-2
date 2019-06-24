<?php

namespace DataCue\MagentoModule\Modules;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use DataCue\MagentoModule\Queue;

class User implements ObserverInterface
{
    /**
     * @param \Magento\Customer\Model\Customer $user
     * @param bool $withId
     * @return array
     */
    public static function buildUserForDataCue($user, $withId = false)
    {
        $item = [
            'email' => $user->getEmail(),
            'timestamp' => str_replace('+00:00', 'Z', gmdate('c', $user->getCreatedAtTimestamp())),
            'first_name' => $user->getFirstname(),
            'last_name' => $user->getLastname(),
        ];

        if ($withId) {
            $item['user_id'] = $user->getId();
        }

        return $item;
    }

    /**
     * @param int $id
     * @return null|\Magento\Customer\Model\Customer
     */
    public static function getUserById($id)
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->create('Magento\Customer\Model\Customer')->load($id);
    }

    private $isNew = false;

    public function __construct()
    {

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        switch ($observer->getEvent()->getName()) {
            case 'customer_save_before':
                $this->setIsNewTag($observer);
                break;
            case 'customer_save_after':
                $this->onUserSaved($observer);
                break;
            case 'customer_delete_after':
                $this->onUserDeleted($observer);
                break;
            default:
                break;
        }
    }

    private function setIsNewTag(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $user \Magento\Customer\Model\Customer
         */
        $user = $observer->getData('data_object');

        $this->isNew = $user->isObjectNew();
    }

    private function onUserSaved(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $user \Magento\Customer\Model\Customer
         */
        $user = $observer->getData('data_object');

        if ($this->isNew) {
            Queue::addJob(
                'create',
                'users',
                $user->getId(),
                [
                    'item' => static::buildUserForDataCue($user, true),
                ]
            );
        } else {
            Queue::addJob(
                'update',
                'users',
                $user->getId(),
                [
                    'userId' => $user->getId(),
                    'item' => static::buildUserForDataCue($user, false),
                ]
            );
        }
    }

    private function onUserDeleted(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $user \Magento\Customer\Model\Customer
         */
        $user = $observer->getData('data_object');

        Queue::addJob(
            'delete',
            'users',
            $user->getId(),
            [
                'userId' => $user->getId(),
            ]
        );
    }
}
