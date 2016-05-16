<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion;

use Database;

/**
 * @Entity
 * @Table(name="VividStorePromotionRewards")
 */
class PromotionReward
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Promotion", inversedBy="promotionRewards")
     */
    protected $promotion;

    /**
     * @OneToOne(targetEntity="PromotionRewardType")
     */
    protected $promotionRewardType;

    /**
     * @Column(type="integer")
     */
    protected $promotionRewardTypeRewardID;

    public function setPromotion($promotion)
    {
        $this->promotion = $promotion;
    }
    public function setPromotionRewardType($promotionRewardType)
    {
        $this->promotionRewardType = $promotionRewardType;
    }
    public function setPromotionRewardTypeRewardID($id)
    {
        $this->promotionRewardTypeRewardID = $id;
    }

    public function getID()
    {
        return $this->id;
    }
    public function getPromotion()
    {
        return $this->promotion;
    }
    public function getPromotionRewardType()
    {
        return $this->promotionRewardType;
    }
    public function getPromotionRewardTypeRewardID()
    {
        return $this->promotionRewardTypeRewardID;
    }
    public function getPromotionRewardTypeReward()
    {
        $promotionRewardTypeController = $this->getPromotionRewardType()->getController();
        $rewardTypeReward = $promotionRewardTypeController->getByID($this->promotionRewardTypeRewardID);
        return $rewardTypeReward;
    }

    public static function getByID($id)
    {
        $db = Database::get();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionReward', $id);
    }
}
