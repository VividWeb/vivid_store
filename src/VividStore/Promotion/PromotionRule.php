<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion;

use Database;

/**
 * @Entity
 * @Table(name="VividStorePromotionRules")
 */
class PromotionRule
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
     * @OneToOne(targetEntity="PromotionRuleType")
     */
    protected $promotionRuleType;

    /**
     * @Column(type="integer")
     */
    protected $promotionRuleTypeRuleID;

    public function setPromotion($promotion)
    {
        $this->promotion = $promotion;
    }
    public function setPromotionRuleType($promotionRuleType)
    {
        $this->promotionRuleType = $promotionRuleType;
    }
    public function setPromotionRuleTypeRuleID($id)
    {
        $this->promotionRuleTypeRuleID = $id;
    }

    public function getID()
    {
        return $this->id;
    }
    public function getPromotion()
    {
        return $this->promotion;
    }
    public function getPromotionRuleType()
    {
        return $this->promotionRuleType;
    }
    public function getPromotionRuleTypeRuleID()
    {
        return $this->promotionRuleTypeRuleID;
    }
    public function getPromotionRuleTypeRule()
    {
        $promotionRuleTypeController = $this->getPromotionRuleType()->getController();
        $ruleTypeRule = $promotionRuleTypeController->getByID($this->promotionRuleTypeRuleID);
        return $ruleTypeRule;
    }

    public static function getByID($id)
    {
        $db = Database::get();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRule', $id);
    }
}
