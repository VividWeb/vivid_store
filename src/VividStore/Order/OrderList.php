<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Order;

use Database;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Pagerfanta\Adapter\DoctrineDbalAdapter;

use Concrete\Package\VividStore\Src\VividStore\Order\Order as StoreOrder;

class OrderList  extends AttributedItemList
{

    protected function getAttributeKeyClassName()
    {
        return '\\Concrete\\Package\\VividStore\\Src\\Attribute\\Key\\StoreOrderKey';
    }
    
    public function createQuery()
    {
        $this->query
        ->select('o.oID')
        ->from('VividStoreOrders','o');

    }

    public function finalizeQuery(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        $paramcount = 0;

        if (isset($this->search)) {
            $this->query->where('oID like ?')->setParameter($paramcount++,'%'. $this->search. '%');
        }

        if(isset($this->status)){
            if ($paramcount > 0) {
                $this->query->andWhere('oStatus = ?')->setParameter($paramcount++,$this->status);
            } else {
                $this->query->where('oStatus = ?')->setParameter($paramcount++,$this->status);
            }
        }
        
        if (isset($this->fromDate)) {
            $this->query->andWhere('DATE(oDate) >= DATE(?)')->setParameter($paramcount++,$this->fromDate);
        }
        if (isset($this->toDate)) {
            $this->query->andWhere('DATE(oDate) <= DATE(?)')->setParameter($paramcount++,$this->toDate);
        }
        if ($this->limit > 0) {
            $this->query->setMaxResults($this->limit);
        }
        
        $this->query->orderBy('oID', 'DESC');

        return $this->query;
    }

    public function setSearch($search) {
        $this->search = $search;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
    public function setFromDate($date = null)
    {
        if(!$date){
            $date = date('Y-m-d', strtotime('-30 days'));
        }
        $this->fromDate = $date;
    }
    public function setToDate($date = null)
    {
        if(!$date){
            $date = date('Y-m-d');
        }
        $this->toDate = $date;
    }
    public function setLimit($limit = 0)
    {
        $this->limit = $limit;
    }
    
    public function getResult($queryRow)
    {
        return StoreOrder::getByID($queryRow['oID']);
    }
    
    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->select('count(distinct o.oID)')->setMaxResults(1);
        });
        $pagination = new Pagination($this, $adapter);
        return $pagination;
    }
    
    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        return $query->select('count(distinct o.oID)')->setMaxResults(1)->execute()->fetchColumn();
    }
    
    public static function getDateOfFirstOrder()
    {
        $db = Database::get();
        $date = $db->GetRow("SELECT * FROM VividStoreOrders ORDER BY oDate ASC LIMIT 1");
        return $date['oDate'];
    }
    public function getOrderItems()
    {
        $orders = $this->getResults();
        $orderItems = array();
        $db = Database::get();
        foreach($orders as $order){
            $oID = $order->getOrderID();
            $OrderOrderItems = $db->GetAll("SELECT * FROM VividStoreOrderItems WHERE oID=?",$oID);
            foreach($OrderOrderItems as $oi){
                $oi = StoreOrder::getByID($oi['oiID']);
                $orderItems[] = $oi;
            }
        }
        return $orderItems;
    }
}
