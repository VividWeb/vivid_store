<?php
namespace Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus;

use Database;
use Events;
use User;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderEvent as StoreOrderEvent;
use \Concrete\Package\VividStore\Src\VividStore\Order\Order as StoreOrder;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;

/**
 * @Entity
 * @Table(name="VividStoreOrderStatusHistories")
 */
class OrderStatusHistory
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $oshID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\VividStore\Src\VividStore\Order\Order",  cascade={"persist"})
     * @JoinColumn(name="oID", referencedColumnName="oID", onDelete="CASCADE")
     */
    protected $order;

    /** @Column(type="text") */
    protected $oshStatus;

    /** @Column(type="datetime") */
    protected $oshDate;

    /** @Column(type="integer", nullable=true) */
    protected $uID;

    public function setOrder($order)
    {
        $this->order = $order;
    }
    public function setOrderStatusHandle($oshStatus)
    {
        $this->oshStatus = $oshStatus;
    }
    public function setDate($date)
    {
        $this->oshDate = $date;
    }
    public function setUserID($uID)
    {
        $this->uID = $uID;
    }

    public function getID()
    {
        return $this->oshID;
    }
    public function getOrder()
    {
        return $this->order;
    }
    public function getOrderStatusHandle()
    {
        return $this->oshStatus;
    }
    public function getOrderStatus()
    {
        return StoreOrderStatus::getByHandle($this->getOrderStatusHandle());
    }
    public function getOrderStatusName()
    {
        $os = $this->getOrderStatus();

        if ($os) {
            return $os->getName();
        } else {
            return null;
        }
    }
    public function getDateTimeObject()
    {
        return $this->oshDate;
    }
    public function getDate($format = 'm/d/Y H:i:s')
    {
        return $this->getDateTimeObject()->format($format);
    }
    public function getUserID()
    {
        return $this->uID;
    }
    public function getUser()
    {
        return User::getByUserID($this->getUserID());
    }
    public function getUserName()
    {
        $u = $this->getUser();
        if ($u) {
            return $u->getUserName();
        }
    }

    public static function getByID($id)
    {
        $db = Database::get();
        $em = $db->getEntityManager();
        return $em->find(get_class(), $id);
    }

    public static function getForOrder(StoreOrder $order)
    {
        $db = Database::get();
        $em = $db->getEntityManager();
        if (!$order->getOrderID()) {
            return false;
        }
        $history = $em->getRepository(get_class())->findBy(array('order'=>$order->getOrderID()), array('oshDate'=>'DESC'));
        return $history;
    }

    public static function updateOrderStatusHistory(StoreOrder $order, $statusHandle)
    {
        $history = self::getForOrder($order);

        if (empty($history) || $history[0]->getOrderStatusHandle() !=$statusHandle) {
            self::recordStatusChange($order, $statusHandle);
            $event = new StoreOrderEvent($order, $order);
            Events::dispatch('on_vividstore_order_status_update', $event);
        }
    }

    private static function recordStatusChange(StoreOrder $order, $statusHandle)
    {
        $user = new user();
        $now = new \DateTime;
        $newHistoryItem = new self();
        $newHistoryItem->setOrder($order);
        $newHistoryItem->setOrderStatusHandle($statusHandle);
        $newHistoryItem->setDate($now);
        $newHistoryItem->setUserID($user->uID);
        $newHistoryItem->save();
        return $newHistoryItem->getOrderStatusHandle();
    }

    public function save()
    {
        $em = \Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    public function delete()
    {
        $em = \Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}
