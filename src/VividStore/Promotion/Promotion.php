<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion;


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
     * @OneToMany(targetEntity="Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionReward", mappedBy="promotion")
     */
    protected $promotionRewards;

    /**
     * @OneToMany(targetEntity="Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRule", mappedBy="promotion")
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

    public function __construct()
    {
        $this->promotionRewards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->promotionRules = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getID()
    {
        return $this->id;
    }
}
