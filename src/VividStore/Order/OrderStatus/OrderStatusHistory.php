<?php
namespace Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus;

use \Concrete\Core\Foundation\Object as Object;
use Database;
use Events;
use User;

use \Concrete\Package\VividStore\Src\VividStore\Order\OrderEvent as StoreOrderEvent;
use \Concrete\Package\VividStore\Src\VividStore\Order\Order as StoreOrder;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus\OrderStatusHistory as StoreOrderStatusH;

/**
 * @Entity
 * @Table(name="CommunityStoreOrderStatusHistories")
 */
class OrderStatusHistory
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $oshID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order",  cascade={"persist"})
     * @JoinColumn(name="oID", referencedColumnName="oID", onDelete="CASCADE")
     */
    protected $order;

    /** @Column(type="text") */
    protected $oshStatus;

    /** @Column(type="datetime") */
    protected $oshDate;

    /** @Column(type="integer", nullable=true) */
    protected $uID;

    public static $table = 'CommunityStoreOrderStatusHistories';

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return StoreOrder::getByID($this->getOrderID());
    }

    public function getOrderStatusHandle()
    {
        return $this->oshStatus;
    }

    public function setOrderStatusHandle($oshStatus)
    {
        $this->oshStatus = $oshStatus;
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

    public function getDate($format = 'm/d/Y H:i:s')
    {
        return date($format, strtotime($this->oshDate));
    }

    public function setDate($date)
    {
        $this->oshDate = $date;
    }

    public function getUserID()
    {
        return $this->uID;
    }

    public function setUserID($uID)
    {
        $this->uID = $uID;
    }

    public function getUser()
    {
        return User::getByUserID($this->getUserID());
    }

    public function getUserName()
    {
        $u = $this->getUser();
        if($u){
            return $u->getUserName();
        }
    }

    private static function getTableName()
    {
        return self::$table;
    }

    private static function getByID($oshID)
    {
        $db = Database::get();
        $em = $db->getEntityManager();
        return $em->find(get_class(), $tcID);
    }

    public static function getForOrder(StoreOrder $order)
    {
        if (!$order->getOrderID()) {
            return false;
        }
        $history = $em->getRepository(get_class())->findBy(array('oID'=>$order->getOrderID()));
        return $history;
    }

    public static function updateOrderStatusHistory(StoreOrder $order, $statusHandle)
    {
        $history = self::getForOrder($order);

        if (empty($history) || $history[0]->getOrderStatusHandle() !=$statusHandle) {
            $updatedOrder = clone $order;
            $updatedOrder->updateStatus(self::recordStatusChange($order, $statusHandle));
            $event = new StoreOrderEvent($updatedOrder, $order);
            Events::dispatch('on_vividstore_order_status_update', $event);
        }
    }

    private static function recordStatusChange(StoreOrder $order, $statusHandle)
    {
        $db = Database::get();
        $user = new user();
        $newHistoryItem = new self();
        $newHistoryItem->setOrder($order);
        $newHistoryItem->setOrderStatusHandle($statusHandle);
        $newHistoryItem->setUserID($user->uID);
        $newHistoryItem->save();
        return $newHistoryItem->getHandle();
    }

}
