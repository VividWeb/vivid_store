<?php
namespace Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation;

use Database;

/**
 * @Entity
 * @Table(name="VividStoreProductVariationOptionItems")
 */
class ProductVariationOptionItem
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $pvoiID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariation", inversedBy="options", cascade={"persist"})
     * @JoinColumn(name="pvID", referencedColumnName="pvID", onDelete="CASCADE")
     */
    protected $variation;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionItem",cascade={"persist"})
     * @JoinColumn(name="poiID", referencedColumnName="poiID", onDelete="CASCADE")
     */
    protected $option;


    public function getID()
    {
        return $this->pvoiID;
    }

    public function setVariation($variation)
    {
        $this->variation = $variation;
    }

    public function getVariation()
    {
        return $this->variation;
    }

    public function setOption($option)
    {
        $this->option = $option;
    }

    public function getOption()
    {
        return $this->option;
    }

    public function save()
    {
        $em = Database::get()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
}
