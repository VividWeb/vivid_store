<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Product\ProductOption;

use Database;

/**
 * @Entity
 * @Table(name="VividStoreProductOptionGroups")
 */
class ProductOptionGroup
{
    
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     */
    protected $pogID;
    
    /**
     * @Column(type="integer")
     */
    protected $pID; 
    
    /**
     * @Column(type="integer")
     */
    protected $pogName;
    
    /**
     * @Column(type="integer")
     */
    protected $pogSort; 
    
    private function setProductID($pID){ $this->pID = $pID; }
    private function setName($name){ $this->pogName = $name; }
    private function setSort($sort){ $this->pogSort = $sort; }
    
    public function getID(){ return $this->piID; }
    public function getProductID() { return $this->pID; }
    public function getName() { return $this->pogName; }
    public function getSort() { return $this->pogSort; }
    
    public static function getByID($id) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionGroup', $id);
    }
    
    public static function getOptionGroupsForProduct(\Concrete\Package\VividStore\Src\VividStore\Product\Product $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionGroup')->findBy(array('pID' => $product->getProductID()));
    }
    
    public static function addProductOptionGroupsForProduct(array $optionGroups, \Concrete\Package\VividStore\Src\VividStore\Product\Product $product)
    {
        //clear out existing product option groups
        $existingOptionGroups = self::getOptionGroupsForProduct($product);
        foreach($existingImages as $img){
            $img->delete();
        }
        
        //add new ones.
        for($i=0;$i<count($images['pifID']);$i++){
            self::add($product->getProductID(),$images['pifID'][$i],$images['piSort'][$i]);
        }
    }
    
    public static function add($product,$name,$sort)
    {
        $productOptionGroup = new self();
        $pID = $product->getProductID();
        return self::addOrUpdate($pID,$name,$sort,$productOptionGroup);
    }
    public function update($product,$name,$sort)
    {
        $productOptionGroup = $this;
        $pID = $product->getProductID();
        return self::addOrUpdate($pID,$name,$sort,$productOptionGroup);
    }
    public static function addOrUpdate($pID,$fID,$sort,$obj)
    {
        $obj->setProductID($pID);
        $obj->setName($name);
        $obj->setSort($sort);
        $obj->save();
        return $obj;
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
