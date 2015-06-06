<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Product;
use Database;
use Concrete\Core\Foundation\Object;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Pagerfanta\Adapter\DoctrineDbalAdapter;

use Concrete\Package\VividStore\Src\VividStore\Product\Product;
defined('C5_EXECUTE') or die(_("Access Denied."));

class ProductList extends AttributedItemList
{
    
    protected $gID = 0;
    protected $sortBy = "alpha";
    protected $featured = "all";
    protected $activeOnly = true;
    
    public function setGroupID($gID)
    {
        $this->gID = $gID;
    }
    
    public function setSortBy($sort)
    {
        $this->sortBy = $sort;
    }
    
    public function setFeatureType($type)
    {
        $this->featured = $type;
    }
    public function activeOnly($bool){
        $this->activeOnly = $bool;
    }
    
    protected function getAttributeKeyClassName()
    {
        return '\\Concrete\\Package\\VividStore\\Src\\Attribute\\Key\\StoreProductKey';
    }
    
    public function createQuery()
    {
        $this->query
        ->select('p.pID')
        ->from('VividStoreProducts','p');
    }

    public function setSearch($search) {
        $this->search = $search;
    }

    
    public function finalizeQuery(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        $paramcount = 0;

        if(isset($this->gID) && ($this->gID > 0)){
            $query->where('gID = ?')->setParameter($paramcount++,$this->gID);
        }
        switch ($this->sortBy){
            case "alpha":
                $query->orderBy('pName','ASC');
                break;
            case "date":
                $query->orderBy('pDateAdded','DESC');
                break;
        }
        switch ($this->featured){
            case "featured":
                $query->andWhere("pFeatured = 1");
                break;
            case "nonfeatured":
                $query->andWhere("pFeatured = 0");
                break;
        }
        if($this->activeOnly){
            $query->andWhere("pActive = 1");
        }

        if ($this->search) {
            $query->andWhere('pName like ?')->setParameter($paramcount++,'%'. $this->search. '%');
        }


        return $query;
    }
    
    public function getResult($queryRow)
    {
        return Product::getByID($queryRow['pID']);
    }
    
    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->select('count(distinct p.pID)')->setMaxResults(1);
        });
        $pagination = new Pagination($this, $adapter);
        return $pagination;
    }
    
    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        return $query->select('count(distinct p.pID)')->setMaxResults(1)->execute()->fetchColumn();
    }
    
}