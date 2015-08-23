<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Orders;

use Database;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Pagerfanta\Adapter\DoctrineDbalAdapter;

use Concrete\Package\VividStore\Src\VividStore\Orders\Order as VividOrder;

defined('C5_EXECUTE') or die(_("Access Denied."));
class OrderItemList  extends ItemList
{
	
	public function orderByPurchased()
	{
		$this->orderByPopularity = true;
	}
    public function createQuery()
    {
        $this->query
        ->select('oi.oiID, COUNT(oi.pID) as popularity')
        ->from('VividStoreOrderItems','oi');

    }

    public function finalizeQuery(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        $paramcount = 0;

		if (isset($this->fromDate)) {
			$this->query->andWhere('DATE(oDate) >= DATE(?)')->setParameter($paramcount++,$this->fromDate);
		}
		if (isset($this->toDate)) {
			$this->query->andWhere('DATE(oDate) <= DATE(?)')->setParameter($paramcount++,$this->toDate);
		}
		if (isset($this->orderByPopularity)){
			$this->query->orderBy('popularity', 'DESC');
		} else {
        	$this->query->orderBy('oiID', 'DESC');
		}

        return $this->query;
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
        return VividOrderItem::getByID($queryRow['oiID']);
    }
    
    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->select('count(distinct oi.oiID)')->setMaxResults(1);
        });
        $pagination = new Pagination($this, $adapter);
        return $pagination;
    }
    
    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        return $query->select('count(distinct oi.oiID)')->setMaxResults(1)->execute()->fetchColumn();
    }
    
}