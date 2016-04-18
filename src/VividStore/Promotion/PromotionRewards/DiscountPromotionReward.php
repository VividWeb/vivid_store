<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewards;

use Package;
use Database;
use Core;
use \Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewardTypeReward as StorePromotionRewardTypeReward;
use \Concrete\Package\VividStore\Src\VividStore\Group\GroupList as StoreGroupList;

/**
 * @Entity
 * @Table(name="VividStorePromotionDiscountRewards")
 */
class DiscountPromotionReward extends StorePromotionRewardTypeReward
{

    public static function getByID($id)
    {
        // TODO: Implement getByID() method.
    }
    public function dashboardForm()
    {
        $this->set('form',Core::make("helper/form"));
        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist",$grouplist);
    }
}
