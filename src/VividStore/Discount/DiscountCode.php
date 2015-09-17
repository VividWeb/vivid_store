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
        return (bool)($this->oID > 0);
    }

    public function remove() {
        $db = Database::get();
        $db->Execute("DELETE FROM VividStoreDiscountCodes  WHERE dcID=?",array($this->dcID));
    }

    static function validate($args)
    {
        $e = Loader::helper('validation/error');

        if(!trim($args['drName'])){
            $e->add(t('You must enter a name'));
        }

        if(!trim($args['drDisplay'])){
            $e->add(t('You must enter display text'));
        }

        if($args['drDeductType']  == 'percentage' && !trim($args['drPercentage'])){
            $e->add(t('You must enter a discount percentage'));
        }

        if($args['drDeductType']  == 'value' && !trim($args['drValue'])){
            $e->add(t('You must enter a discount value'));
        }

        return $e;

    }

}
