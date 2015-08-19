<?php

namespace Concrete\Package\VividStore\Src\Vividstore\Discount;
use Concrete\Core\Foundation\Object as Object;
use Concrete\Package\VividStore\Src\Vividstore\Discount\DiscountCode;
use Database;
use Core;

class DiscountRule extends Object
{
    public static function getByID($drID)
    {
        $db = Database::get();
        $data = $db->GetRow("SELECT * FROM VividStoreDiscountRules WHERE drID=?",$drID);
        return self::load($data);
    }

    public static function discountsWithCodesExist() {
        $db = Database::get();
        $data = $db->GetRow("SELECT count(*) as codecount FROM VividStoreDiscountRules WHERE drEnabled =1 "); // TODO

        return ($data['codecount'] > 0);
    }

    public static function findAutomaticDiscounts(Cart $cart, $code = '', $user = null) {
        $db = Database::get();
        $r = $db->GetAssoc("SELECT * FROM VividStoreDiscountRules WHERE enabled =1 "); // TODO
        $discounts = array();
        return $discounts;
    }


    public function retrieveStatistics() {
        $db = Database::get();
        $r = $db->query("select count(*) as total, COUNT(CASE WHEN oID is NULL THEN 1 END) AS available from VividStoreDiscountCodes where drID = ?", array($this->drID));
        $r = $r->fetchRow();
        $this->totalCodes = $r['total'];
        $this->availableCodes = $r['available'];
        return $r;
    }

    public static function findDiscountRuleByCode($code) {
        // look up discount by code, if found and applicable:
        // return $discount;

        return false;
    }

    public function getCodes() {

        if ($this->drID) {
            $codes = array();

            $db = Database::get();
            $result = $db->query("SELECT * from VividStoreDiscountCodes as vsdc
                                  LEFT JOIN VividStoreOrders as vso on vsdc.oID = vso.oID
                                  WHERE drID=?", array($this->drID));

            while ($row = $result->fetchRow()) {
                $codes[] = $row;
            }

            return $codes;

        } else {
            return false;
        }

    }

    public function load($data) {
        if(!empty($data)){
            $discountRule = new DiscountRule();
            $discountRule->setPropertiesFromArray($data);
        }
        return($discountRule instanceof DiscountRule) ? $discountRule : false;
    }

    public function save($data)
    {
        $db = Database::get();

        if($data['drID']){
            //if we know the drID, we're updating.

            if ($data['validFrom'] == '0') {
                $data['drValidFrom_dt'] = null;
            }

            if ($data['validTo'] == '0') {
                $data['drValidTo_dt'] = null;
            }

            //update discount details
            $vals = array(
                $data['drName'],
                $data['drDisplay'],
                $data['drEnabled'],
                $data['drDescription'],
                $data['drDeductType'],
                $data['drValue'],
                $data['drPercentage'],
                $data['drDeductFrom'],
                $data['drTrigger'],
                $data['drSingleUseCodes'],
                $data['drExclusive'],
                $data['drCurrency'],
                $data['drValidFrom_dt'],
                $data['drValidTo_dt'],
                $data['drID']
            );

            $drID = $data['drID'];

            $db->Execute('UPDATE VividStoreDiscountRules SET drName=?, drDisplay=?, drEnabled=?, drDescription=?, drDeductType=?, drValue=?, drPercentage=?, drDeductFrom=?, drTrigger=?, drSingleUseCodes=?, drExclusive=?, drCurrency=?, drValidFrom=?, drValidTo=? WHERE drID = ?', $vals);


        } else {
            //else, we don't know it, so we're adding

            $dt = Core::make('helper/date');
            $now = $dt->getLocalDateTime();

            //add discount details
            $vals = array(
                $data['drName'],
                $data['drDisplay'],
                $data['drEnabled'],
                $data['drDescription'],
                $data['drDeductType'],
                $data['drValue'],
                $data['drPercentage'],
                $data['drDeductFrom'],
                $data['drTrigger'],
                $data['drSingleUseCodes'],
                $data['drExclusive'],
                $data['drExclusive'],
                $data['drCurrency'],
                $data['drValidFrom_dt'],
                $data['drValidTo_dt'],
                $now);


            $db->Execute("INSERT INTO VividStoreDiscountRules (
                                        drName,
                                        drDisplay,
                                        drEnabled,
                                        drDescription,
                                        drDeductType,
                                        drValue,
                                        drPercentage,
                                        drDeductFrom,
                                        drTrigger,
                                        drSingleUseCodes,
                                        drExclusive,
                                        drCurrency,
                                        drValidFrom,
                                        drValidTo,
                                        drDateAdded) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",$vals);

            $drID = $db->lastInsertId();
        }

        $discountRule = DiscountRule::getByID($drID);
        return $discountRule;
    }


    public function remove() {
        $db = Database::get();

        $dt = Core::make('helper/date');
        $now = $dt->getLocalDateTime();

        $db->Execute("UPDATE VividStoreDiscountRules SET drDeleted = ? WHERE drID=?",array($now, $this->drID));
    }

}