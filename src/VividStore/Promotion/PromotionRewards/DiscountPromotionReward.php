<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewards;

use Database;
use Core;
use \Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewardType as StorePromotionRewardType;
use \Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewardTypeReward as StorePromotionRewardTypeReward;
use \Concrete\Package\VividStore\Src\VividStore\Group\GroupList as StoreGroupList;

/**
 * @Entity
 * @Table(name="VividStorePromotionDiscountRewards")
 */
class DiscountPromotionReward extends StorePromotionRewardTypeReward
{
    /**
     * @Column(type="string")
     */
    protected $discountCalculation;

    /**
     * @Column(type="decimal", precision=10, scale=2)
     */
    protected $discountAmount;

    /**
     * @Column(type="string")
     */
    protected $discountSubject;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $discountTarget;

    public function setDiscountCalculation($calculation)
    {
        $this->discountCalculation = $calculation;
    }
    public function setDiscountAmount($amount)
    {
        $this->discountAmount = $amount;
    }
    public function setDiscountSubject($subject)
    {
        $this->discountSubject = $subject;
    }
    public function setDiscountTarget($targetID)
    {
        $this->discountTarget = $targetID;
    }

    public function getDiscountCalculation()
    {
        return $this->discountCalculation;
    }
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }
    public function getDiscountSubject()
    {
        return $this->discountSubject;
    }
    public function getDiscountTarget()
    {
        return $this->discountTarget;
    }
    
    public static function getByID($id)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find(get_class(), $id);
    }
    public function dashboardForm()
    {
        $this->set('form', Core::make("helper/form"));
        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist", $grouplist);
        $this->set("rewardType", StorePromotionRewardType::getByHandle('discount'));
    }
    public static function addReward($data)
    {
        $reward = new self();
        $reward->setPromotionID($data['promotionID']);
        $reward->setDiscountCalculation($data['discountCalculation']);
        $reward->setDiscountAmount($data['discountAmount']);
        $reward->setDiscountSubject($data['discountSubject']);
        $reward->setDiscountTarget($data['discountTarget']);
        $reward->save();
        return $reward;
    }
    public function update($data)
    {
        // TODO: Implement update() method.
    }
    public function performReward()
    {
        // TODO: Implement performReward() method.
    }
}
