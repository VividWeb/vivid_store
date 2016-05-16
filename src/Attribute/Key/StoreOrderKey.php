<?php
namespace Concrete\Package\VividStore\Src\Attribute\Key;

use Database;
use \Concrete\Core\Attribute\Value\ValueList as AttributeValueList;
use \Concrete\Package\VividStore\Src\Attribute\Value\StoreOrderValue as StoreOrderValue;
use \Concrete\Core\Attribute\Key\Key as Key;

/**
 * @Entity
 * @Table(name="VividStoreOrderAttributeKeys")
 */
class StoreOrderKey extends Key
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $akID;

    public function getAttributes($oID, $method = 'getValue')
    {
        $db = Database::connection();
        $values = $db->GetAll("select akID, avID from VividStoreOrderAttributeValues where oID = ?", array($oID));
        $avl = new AttributeValueList();
        foreach ($values as $val) {
            $ak = StoreOrderKey::getByID($val['akID']);
            if (is_object($ak)) {
                $value = $ak->getAttributeValue($val['avID'], $method);
                $avl->addAttributeValue($ak, $value);
            }
        }
        return $avl;
    }
    
    public function load($akID)
    {
        parent::load($akID);
        $db = Database::get();
        $row = $db->GetRow("select * from VividStoreOrderAttributeKeys where akID = ?", array($akID));
        $this->setPropertiesFromArray($row);
    }
    
    public function getAttributeValue($avID, $method = 'getValue')
    {
        $av = StoreOrderValue::getByID($avID);
        $av->setAttributeKey($this);
        return $av->{$method}();
    }
       
    public static function getByID($akID)
    {
        $ak = new StoreOrderKey();
        $ak->load($akID);
        if ($ak->getAttributeKeyID() > 0) {
            return $ak;
        }
    }

    public static function getByHandle($akHandle)
    {
        $db = Database::get();
        $q = "SELECT ak.akID
            FROM AttributeKeys ak
            INNER JOIN AttributeKeyCategories akc ON ak.akCategoryID = akc.akCategoryID
            WHERE ak.akHandle = ?
            AND akc.akCategoryHandle = 'store_order'";
        $akID = $db->GetOne($q, array($akHandle));
        if ($akID > 0) {
            $ak = StoreOrderKey::getByID($akID);
        }
        if ($ak === -1) {
            return false;
        }
        return $ak;
    }
    
    
    public static function getList()
    {
        return parent::getList('store_order');
    }
    
    protected function saveAttribute($order, $value = false)
    {
        $av = $order->getAttributeValueObject($this, true);
        parent::saveAttribute($av, $value);
        $db = Database::get();
        $v = array($order->getOrderID(), $this->getAttributeKeyID(), $av->getAttributeValueID());
        $db->Replace('VividStoreOrderAttributeValues', array(
            'oID' => $order->getOrderID(),
            'akID' => $this->getAttributeKeyID(),
            'avID' => $av->getAttributeValueID()
        ), array('oID', 'akID'));
        unset($av);
    }
    
    public static function add($type, $args, $pkg = false)
    {
        $ak = parent::add('store_order', $type, $args, $pkg);
        
        extract($args);
        
        $v = array($ak->getAttributeKeyID());
        $db = Database::get();
        $db->Execute('REPLACE INTO VividStoreOrderAttributeKeys (akID) VALUES (?)', $v);
        
        $nak = new StoreOrderKey();
        $nak->load($ak->getAttributeKeyID());
        return $ak;
    }
    
    public function update($args)
    {
        $ak = parent::update($args);
        extract($args);
        $v = array($ak->getAttributeKeyID());
        $db = Database::get();
        $db->Execute('REPLACE INTO VividStoreOrderAttributeKeys (akID) VALUES (?)', $v);
    }

    public function delete()
    {
        parent::delete();
        $db = Database::get();
        $r = $db->Execute('select avID from VividStoreOrderAttributeValues where akID = ?', array($this->getAttributeKeyID()));
        while ($row = $r->FetchRow()) {
            $db->Execute('delete from AttributeValues where avID = ?', array($row['avID']));
        }
        $db->Execute('delete from VividStoreOrderAttributeValues where akID = ?', array($this->getAttributeKeyID()));
    }
}
