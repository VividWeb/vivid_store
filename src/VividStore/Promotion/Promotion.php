<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion;

use Package;
use Database;


/**
 * @Entity
 * @Table(name="VividStorePromotions")
 */
class Promotion
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="string")
     */
    protected $label;

    /**
     * @Column(type="boolean")
     */
    protected $enabled;

    /**
     * Bidirectional - Many Promotions have Many Rewards (OWNING SIDE)
     *
     * @ManyToMany(targetEntity="Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionReward")
     * @JoinTable(name="VividStorePromotionRewards")
     */
    protected $promotionRewards;

    /**
     * Bidirectional - Many Promotions have Many Rules (OWNING SIDE)
     *
     * @ManyToMany(targetEntity="Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRule")
     * @JoinTable(name="VividStorePromotionRules")
     */
    protected $promotionRules;

    /**
     * @Column(type="boolean")
     */
    protected $exclusive;

    /**
     * @Column(type="boolean")
     */
    protected $showSalePrice;

    public function __construct() {
        $this->promotionRewards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->promotionRules = new \Doctrine\Common\Collections\ArrayCollection();
    }
}
