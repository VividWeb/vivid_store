<?php
namespace Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation;

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariationOptionItem as StoreProductVariationOptionItem;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;
use Doctrine\Common\Collections\ArrayCollection;
use Database;

/**
 * @Entity
 * @Table(name="VividStoreProductVariations")
 */
class ProductVariation
{

    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $pvID;

    /**
     * @Column(type="integer")
     */
    protected $pID;

    /**
     * @Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pvPrice;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $pvSKU;

    /**
    * @Column(type="decimal", precision=10, scale=2, nullable=true)
    */
    protected $pvSalePrice;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $pvQty;

    /**
     * @Column(type="boolean",nullable=true)
     */
    protected $pvQtyUnlim;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $pvActive;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $pWidth;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $pHeight;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $pLength;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $pWeight;

    /**
     * @OneToMany(targetEntity="Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariationOptionItem", mappedBy="variation"))
     */
    protected $options;


    public function __construct() {
        $this->options = new ArrayCollection();
    }

    public function getOptions() {
        return $this->options;
    }


    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->pvID;
    }

    /**
     * @return mixed
     */
    public function getProductID()
    {
        return $this->pID;
    }

    /**
     * @return mixed
     */
    public function getVariationSKU()
    {
        return $this->pvSKU;
    }

    /**
     * @param mixed $pvSKU
     */
    public function setVariationSKU($pvSKU)
    {
        $this->pvSKU = $pvSKU;
    }


    /**
     * @param mixed $pID
     */
    public function setProductID($pID)
    {
        $this->pID = $pID;
    }

    /**
     * @return mixed
     */
    public function getVariationPrice()
    {
        return $this->pvPrice;
    }

    /**
     * @param mixed $pvPrice
     */
    public function setVariationPrice($pvPrice)
    {
        $this->pvPrice = $pvPrice;
    }

    /**
     * @return mixed
     */
    public function getVariationSalePrice()
    {
        return $this->pvSalePrice;
    }

    /**
     * @param mixed $pvSalePrice
     */
    public function setVariationSalePrice($pvSalePrice)
    {
        $this->pvSalePrice = $pvSalePrice;
    }

    /**
     * @return mixed
     */
    public function getVariationQty()
    {
        return $this->pvQty;
    }

    /**
     * @param mixed $pvQty
     */
    public function setVariationQty($pvQty)
    {
        $this->pvQty = $pvQty;
    }

    /**
     * @return mixed
     */
    public function getVariationQtyUnlim()
    {
        return $this->pvQtyUnlim;
    }

    /**
     * @param mixed $pvQtyUnlim
     */
    public function setVariationQtyUnlim($pvQtyUnlim)
    {
        $this->pvQtyUnlim = $pvQtyUnlim;
    }

    /**
     * @return mixed
     */
    public function getVariationActive()
    {
        return $this->pvActive;
    }

    /**
     * @param mixed $pvActive
     */
    public function setVariationActive($pvActive)
    {
        $this->pvActive = $pvActive;
    }

    /**
     * @return mixed
     */
    public function getVariationWidth()
    {
        return $this->pWidth;
    }

    /**
     * @param mixed $pWidth
     */
    public function setVariationWidth($pWidth)
    {
        $this->pWidth = $pWidth;
    }

    /**
     * @return mixed
     */
    public function getVariationHeight()
    {
        return $this->pHeight;
    }

    /**
     * @param mixed $pHeight
     */
    public function setVariationHeight($pHeight)
    {
        $this->pHeight = $pHeight;
    }

    /**
     * @return mixed
     */
    public function getVariationLength()
    {
        return $this->pLength;
    }

    /**
     * @param mixed $pLength
     */
    public function setVariationLength($pLength)
    {
        $this->pLength = $pLength;
    }

    /**
     * @return mixed
     */
    public function getVariationWeight()
    {
        return $this->pWeight;
    }

    /**
     * @param mixed $pWeight
     */
    public function setVariationWeight($pWeight)
    {
        $this->pWeight = $pWeight;
    }

    public function isUnlimited() {
        return $this->getVariationQtyUnlim();
    }

    public static function addVariations(array $data, StoreProduct $product) {
        foreach($data['option_combo'] as $key=>$optioncombo) {
            $optionvalues = explode('_', $optioncombo);

            $variation = self::getByOptionItemIDs($optionvalues);

            if (!$variation) {
                $variation = self::add(
                    $product->getProductID(),
                    $data['pvSKU'][$key],
                    $data['pvPrice'][$key],
                    $data['pvQty'][$key],
                    $data['pvQtyUnlim'][$key]
                );

                foreach($optionvalues as $optionvalue) {
                    $option = StoreProductOptionItem::getByID($optionvalue);

                    if ($option) {
                        $variationoption = new StoreProductVariationOptionItem();
                        $variationoption->setOption($option);
                        $variationoption->setVariation($variation);
                        $variationoption->save();
                    }
                }
            } else {
                $variation->setVariationSKU($data['pvSKU'][$key]);
                $variation->setVariationPrice($data['pvPrice'][$key]);
                $variation->setVariationQty($data['pvQty'][$key]);
                $variation->setVariationQtyUnlim($data['pvQtyUnlim'][$key]);
                $variation->save();

            }
        }
    }

    public static function getByID($pvID) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariation', $pvID);
    }

    public static function add($productID, $sku, $price, $qty, $qtyUnlim)
    {
        $variation = new self();
        $variation->setProductID($productID);
        $variation->setVariationSKU($sku);
        $variation->setVariationPrice($price);
        $variation->setVariationQty($qty);
        $variation->setVariationQtyUnlim($qtyUnlim);
        $variation->save();
        return $variation;
    }

    public static function getByOptionItemIDs(array $optionids) {
        $db = \Database::connection();

        if (is_array($optionids) && !empty($optionids)) {
            $options = implode(',', $optionids);
            $pvID = $db->fetchColumn("SELECT pvID FROM VividStoreProductVariationOptionItems WHERE poiID in ($options)
                                 group by pvID having count(*) = ?", array(count($optionids)));

            return self::getByID($pvID);
        }

        return false;
    }

    public function save()
    {
        $em = Database::get()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public static function getVariationsForProduct(StoreProduct $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariation')->findBy(array('pID' => $product->getProductID()));
    }


}
