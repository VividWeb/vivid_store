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
    
    protected $gIDs = array();
    protected $groupMatchAny = false;
    protected $sortBy = "alpha";
    protected $featured = "all";
    protected $activeOnly = true;
    protected $cIDs = array();
    
    public function setGroupID($gID)
    {
        $this->gIDs = array($gID);
    }

    public function setGroupIDs($groupIDs)
    {
        $this->gIDs = array_merge($this->gIDs, $groupIDs);
    }


    public function setSortBy($sort)
    {
        $this->sortBy = $sort;
    }

    public function setCID($cID) {
        $this->cIDs[] = $cID;
    }

    public function setCIDs($cIDs) {
        $this->cIDs = array_merge($this->cIDs, array_values($cIDs));
    }

    public function setGroupMatchAny($match) {
        $this->groupMatchAny = (bool)$match;
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

        if(!empty($this->gIDs)) {
            $validgids = array();

            foreach($this->gIDs as $gID) {
                if ($gID > 0) {
                    $validgids[] = $gID;
                }
            }

            if (!empty($validgids)) {
                $query->innerJoin('p', 'VividStoreProductGroups', 'g', 'p.pID = g.pID and g.gID in (' . implode(',', $validgids) . ')');

                if (!$this->groupMatchAny) {
                    $query->having('count(g.gID) = '  . count($validgids));
                }
            }
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

        if (is_array($this->cIDs) && !empty($this->cIDs)) {
            $query->innerJoin('p', 'VividStoreProductLocations', 'l', 'p.pID = l.pID and l.cID in (' .  implode(',',$this->cIDs). ')');
        }

        $query->groupBy('p.pID');

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