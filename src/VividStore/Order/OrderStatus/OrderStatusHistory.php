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

class OrderStatusHistory extends Object
{
    public static $table = 'VividStoreOrderStatusHistories';

    public function getOrderID() {
        return $this->oID;
    }

    public function getOrder() {
        return StoreOrder::getByID($this->getOrderID());
    }

    public function getOrderStatusHandle() {
        return $this->oshStatus;
    }

    public function getOrderStatus() {
        return StoreOrderStatus::getByHandle($this->getOrderStatusHandle());
    }

    public function getOrderStatusName() {
        return $this->getOrderStatus()->getName();
    }

    public function getDate($format = 'm/d/Y H:i:s') {
        return date($format, strtotime($this->oshDate));
    }

    public function getUserID() {
        return $this->uID;
    }

    public function getUser() {
        return User::getByUserID($this->getUserID());
    }

    public function getUserName() {
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
        $data = $db->GetRow("SELECT * FROM " . self::getTableName() . " WHERE oshID=?", $oshID);
        $history = null;
        if (!empty($data)) {
            $history = new OrderStatusHistory();
            $history->setPropertiesFromArray($data);
        }
        return ($history instanceof OrderStatusHistory) ? $history : false;
    }

    public static function getForOrder(StoreOrder $order)
    {
        if (!$order->getOrderID()) {
            return false;
        }
        $sql = "SELECT * FROM " . self::$table . " WHERE oID=? ORDER BY oshDate DESC";
        $rows = Database::get()->getAll($sql, $order->getOrderID());
        $history = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $history[] = self::getByID($row['oshID']);
            }
        }
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
        $newOrderStatus = StoreOrderStatus::getByHandle($statusHandle);
        $user = new user();

        $statusHistorySql = "INSERT INTO " . self::$table . " SET oID=?, oshStatus=?, uID=?";
        $statusHistoryValues = array(
            $order->getOrderID(),
            $newOrderStatus->getHandle(),
            $user->uID
        );
        $db->Execute($statusHistorySql, $statusHistoryValues);
        return $newOrderStatus->getHandle();
    }

}
