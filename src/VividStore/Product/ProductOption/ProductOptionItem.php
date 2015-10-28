<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Product\ProductOption;

use Database;

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product\ProductOption\ProductOptionGroup as StoreProductOptionGroup;

/**
 * @Entity
 * @Table(name="VividStoreProductOptionItems")
 */
class ProductOptionItem
{

    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $poiID;

    /**
     * @Column(type="integer")
     */
    protected $pID;

    /**
     * @Column(type="integer")
     */
    protected $pogID;

    /**
     * @Column(type="string")
     */
    protected $poiName;

    /**
     * @Column(type="integer")
     */
    protected $poiSort;

    private function setProductID($pID){ $this->pID = $pID; }
    private function setProductOptionGroupID($id){ $this->pogID = $id; }
    private function setProductOptionItemName($name){ $this->poiName = $name; }
    private function setSort($sort){ $this->poiSort = $sort; }
    private function setName($name){ $this->poiName = $name; }

    public function getID(){ return $this->poiID; }
    public function getProductID() { return $this->pID; }
    public function getProductOptionGroupID() { return $this->pogID; }
    public function getName(){ return $this->poiName; }
    public function getSort() { return $this->poiSort; }

    public static function getByID($id) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionItem', $id);
    }

    public static function getOptionItemsForProduct(StoreProduct $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionItem')->findBy(array('pID' => $product->getProductID()), array('poiSort'=>'asc'));
    }

    public static function getOptionItemsForProductOptionGroup(ProductOptionGroup $pog)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionItem')->findBy(array('pogID' => $pog->getID()), array('poiSort'=>'asc'));
    }

    public static function removeOptionItemsForProduct(StoreProduct $product, $excluding = array())
    {
        if (!is_array($excluding)) {
            $excluding = array();
        }

        //clear out existing product option items
        $existingOptionItems = self::getOptionItemsForProduct($product);
        foreach($existingOptionItems as $optionItem){
            if (!in_array($optionItem->getID(), $excluding)) {
                $optionItem->delete();
            }
        }
    }

    public static function add(StoreProduct $product,$pogID,$name,$sort)
    {
        $productOptionItem = new self();
        $pID = $product->getProductID();
        $productOptionItem->setProductID($pID);
        $productOptionItem->setProductOptionGroupID($pogID);
        $productOptionItem->setProductOptionItemName($name);
        $productOptionItem->setSort($sort);
        $productOptionItem->save();
        return $productOptionItem;
    }


    public function update(StoreProduct $product,$name,$sort)
    {
        $pID = $product->getProductID();
        $this->setProductID($pID);
        $this->setName($name);
        $this->setSort($sort);
        $this->save();
        return $this;
    }

    public function save()
    {
        $em = Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }

}
