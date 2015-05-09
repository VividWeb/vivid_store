<?php
namespace Concrete\Package\VividStore\Src\VividStore\Orders\OrderStatus;

use \Concrete\Core\Foundation\Object as Object;
use \Concrete\Package\VividStore\Src\VividStore\Orders\OrderStatus\OrderStatus;
use \Concrete\Package\VividStore\Src\VividStore\Orders\OrderEvent;
use \Concrete\Package\VividStore\Src\VividStore\Orders\Order;
use Database;
use Events;
use User;

class History extends Object
{
    public static $table = 'VividStoreOrderStatusHistories';

    public function getOrderID() {
        return $this->oID;
    }

    public function getOrder() {
        return Order::getByID($this->getOrderID());
    }

    public function getOrderStatusHandle() {
        return $this->oshStatus;
    }

    public function getOrderStatus() {
        return OrderStatus::getByHandle($this->getOrderStatusHandle());
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
        return $this->getUser()->getUserName();
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
            $history = new History();
            $history->setPropertiesFromArray($data);
        }
        return ($history instanceof History) ? $history : false;
    }

    public static function getForOrder(Order $order)
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

    public static function updateOrderStatusHistory(Order $order, $statusHandle)
    {
        if ($order->getStatus()!=$statusHandle) {
            $updatedOrder = clone $order;
            $updatedOrder->oStatus = self::recordStatusChange($order, $statusHandle);
            $event = new OrderEvent($updatedOrder, $order);
            Events::dispatch('on_vividstore_order_status_update', $event);
        }
    }

    private static function recordStatusChange(Order $order, $statusHandle)
    {
        $db = Database::get();
        $newOrderStatus = OrderStatus::getByHandle($statusHandle);
        $user = new user();

        $statusHistorySql = "INSERT INTO " . self::$table . " SET oID=?, oshStatus=?, uID=?";
        $statusHistoryValues = array(
            $order->getOrderID(),
            $newOrderStatus->getHandle(),
            $user->uID
        );
        $db->Execute($statusHistorySql, $statusHistoryValues);

        $updateOrderSql = "UPDATE VividStoreOrders SET oStatus = ? WHERE oID = ?";
        $updateOrderValues = array(
            $newOrderStatus->getHandle(),
            $order->getOrderID()
        );
        $db->Execute($updateOrderSql, $updateOrderValues);

        return $newOrderStatus->getHandle();
    }

}
