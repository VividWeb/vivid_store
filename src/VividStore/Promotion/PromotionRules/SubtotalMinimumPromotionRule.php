<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRules;

use Package;
use Database;
use Core;
use \Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRuleTypeRule as StorePromotionRuleTypeRule;

/**
 * @Entity
 * @Table(name="VividStorePromotionSubtotalMinimumRule")
 */
class SubtotalMinimumPromotionRule extends StorePromotionRuleTypeRule
{

    public static function getByID($id)
    {
        // TODO: Implement getByID() method.
    }
    public function dashboardForm()
    {
        $this->set('form',Core::make("helper/form"));
    }
}
