<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Orders;

use Database;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Pagerfanta\Adapter\DoctrineDbalAdapter;

use Concrete\Package\VividStore\Src\VividStore\Orders\Order as VividOrder;
use Concrete\Package\VividStore\Src\VividStore\Orders\Item as OrderItem;

defined('C5_EXECUTE') or die(_("Access Denied."));
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
        ->from('VividStoreOrder','o')
        ->orderBy('oID', 'DESC');
    }
    
    public function getResult($queryRow)
    {
        return VividOrder::getByID($queryRow['oID']);
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
    
}