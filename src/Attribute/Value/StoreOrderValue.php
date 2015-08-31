<?php
namespace Concrete\Package\VividStore\Src\Attribute\Value;

use Database;
use \Concrete\Core\Attribute\Value\Value as Value;
defined('C5_EXECUTE') or die(_("Access Denied."));
class StoreOrderValue extends Value {

    public function setOrder($order) {
        $this->order = $order;
    }
    
    public static function getByID($avID) {
        $cav = new StoreOrderValue();
        $cav->load($avID);
        if ($cav->getAttributeValueID() == $avID) {
            return $cav;
        }
    }

    public function delete() {
        $db = Database::get();
        $db->Execute('delete from VividStoreOrderAttributeValues where oID = ? and akID = ? and avID = ?', array(
            $this->order->getOrderID(),
            $this->attributeKey->getAttributeKeyID(),
            $this->getAttributeValueID()
        ));

        // Before we run delete() on the parent object, we make sure that attribute value isn't being referenced in the table anywhere else
        $num = $db->GetOne('select count(avID) from VividStoreOrderAttributeValues where avID = ?', array($this->getAttributeValueID()));
        if ($num < 1) {
            parent::delete();
        }
    }
}
