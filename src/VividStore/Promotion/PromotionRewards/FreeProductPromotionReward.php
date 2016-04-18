<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewards;

use Package;
use Database;
use Core;
use \Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewardTypeReward as StorePromotionRewardTypeReward;


/**
 * @Entity
 * @Table(name="VividStorePromotionFreeProductRewards")
 */
class FreeProductPromotionReward extends StorePromotionRewardTypeReward
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
