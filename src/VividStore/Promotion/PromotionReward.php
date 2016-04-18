<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion;

use Package;
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
     * @ManyToOne(targetEntity="Promotion", inversedBy="promotionRules")
     */
    protected $promotion;

    /**
     * @Column(type="integer")
     */
    protected $promotionRewardType;
}
