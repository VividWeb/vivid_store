<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion;

use Package;
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
     * @ManyToOne(targetEntity="Promotion", inversedBy="promotionRules")
     */
    protected $promotion;

    /**
     * @OneToOne(targetEntity="Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRuleType", mappedBy="promotionRule")
     */
    protected $promotionRuleType;

    /**
     * @Column(type="integer")
     */
    protected $promotionRuleTypeRuleID;
}
