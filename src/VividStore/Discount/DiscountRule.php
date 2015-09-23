<?php

namespace Concrete\Package\VividStore\Src\Vividstore\Discount;

use Concrete\Core\Foundation\Object as Object;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as StorePrice;
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

    public static function findAutomaticDiscounts($user = null, $productlist = array()) {
        $db = Database::get();
        $result = $db->query("SELECT * FROM VividStoreDiscountRules
              WHERE drEnabled = 1
              AND drDeleted IS NULL
              AND drTrigger = 'auto'
              AND (drPercentage > 0 or drValue  > 0)
              AND (drValidFrom IS NULL OR drValidFrom <= NOW())
              AND (drValidTo IS NULL OR drValidTo > NOW())
              "); // TODO

        $discounts = array();
        while ($row = $result->fetchRow()) {
            $discounts[] =  self::load($row);
        }
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

    public static function findDiscountRuleByCode($code, $user = null) {
        $db = Database::get();

        $result = $db->query("SELECT * FROM VividStoreDiscountCodes as dc
        LEFT JOIN VividStoreDiscountRules as dr on dc.drID = dr.drID
        WHERE dcCode = ?
        AND oID IS NULL
        AND drDeleted IS NULL
        AND  drEnabled = '1'
        AND drTrigger = 'code'
        AND (drValidFrom IS NULL OR drValidFrom <= NOW())
        AND (drValidTo IS NULL OR drValidTo > NOW())", array($code));

        $discounts = array();

        while ($row = $result->fetchRow()) {
            $discounts[] =  self::load($row);
        }

        return $discounts;
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

    public static function load($data) {
        if(!empty($data)){
            $discountRule = new DiscountRule();
            $discountRule->setPropertiesFromArray($data);
        }
        return($discountRule instanceof DiscountRule) ? $discountRule : false;
    }

    public function save($data)
    {
        $db = Database::get();

        if ($data['validFrom'] == '0') {
            $data['drValidFrom_dt'] = null;
        }

        if ($data['validTo'] == '0') {
            $data['drValidTo_dt'] = null;
        }

        if($data['drID']){
            //if we know the drID, we're updating.

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

    public function getDisplay() {
        $display = trim($this->drDisplay);

        if ($display) {
           return $display;
        } else {
            if ($this->drDeductType == 'percentage') {
                return $this->drPercentage . ' ' . t('off');
            }

            if ($this->drDeductType == 'value') {
                return StorePrice::format($this->drValue) . ' ' . t('off');
            }
        }
    }

    public function isSingleUse() {
        return (bool)$this->drSingleUseCodes;
    }

    public function requiresCode() {
        return ($this->drTrigger == 'code');
    }

}
