<?php

namespace Concrete\Package\VividStore\Src\Vividstore\Discount;

use Concrete\Core\Foundation\Object as Object;
use Database;
use Loader;

class DiscountCode extends Object
{
    static function getByID($dcID) {
        $db = Database::get();
        $data = $db->GetRow("SELECT * FROM VividStoreDiscountCodes WHERE dcID=?",$dcID);
        return self::load($data);
    }

    static function getByCode($code) {
        $db = Database::get();
        $data = $db->GetRow("SELECT * FROM VividStoreDiscountCodes WHERE dcCode=?",$code);
        return self::load($data);
    }

    public function load($data) {
        if(!empty($data)){
            $discountCode = new DiscountCode();
            $discountCode->setPropertiesFromArray($data);
        }
        return($discountCode instanceof DiscountCode) ? $discountCode : false;
    }

    static function add($discountRuleID, $code) {
        $db = Database::get();
        $vals = array($discountRuleID, strtoupper($code));

        $data = $db->GetRow("SELECT * FROM VividStoreDiscountCodes WHERE dcCode=? and oID is null",strtoupper($code));
        if (!$data['dcCode'])  {
            $db->Execute("INSERT INTO VividStoreDiscountCodes (drID,dcCode) VALUES (?,?)",$vals);
            return true;
        }

        return false;
    }

    public function isUsed() {
        return (bool)($this->oID > 0);
    }

    public function markUsed($oID) {
        $db = Database::get();
        $db->Execute("UPDATE VividStoreDiscountCodes set oID = ?  WHERE dcID=?",array((int)$oID, $this->dcID));
    }

    public function remove() {
        $db = Database::get();
        $db->Execute("DELETE FROM VividStoreDiscountCodes  WHERE dcID=?",array($this->dcID));
    }

    static function validate($args)
    {
        $e = Loader::helper('validation/error');

        return $e;

    }

}
