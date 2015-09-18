<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Product;

use Database;

/**
 * @Entity
 * @Table(name="VividStoreProductGroups")
 */
class ProductGroup
{
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     */
    protected $pgID;
    
    /**
     * @Column(type="integer")
     */
    protected $pID;
    
    /**
     * @Column(type="integer")
     */
    protected $gID; 
    
    private function setProductID($pID){ $this->pID = $pID; }
    private function setGroupID($gID){ $this->gID = $gID; }
    
    public function getProductID(){ return $this->pID; }
    public function getGroupID() { return $this->gID; }
    
    public static function getByID($pgID) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Product\ProductGroup', $pgID);
    }
    
    public static function getGroupsForProduct(\Concrete\Package\VividStore\Src\VividStore\Product\Product $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\VividStore\Src\VividStore\Product\ProductGroup')->findBy(array('pID' => $product->getProductID()));
    }
    
    public static function addGroupsForProduct(array $data, \Concrete\Package\VividStore\Src\VividStore\Product\Product $product)
    {
        //clear out existing groups
        $existingGroups = self::getGroupsForProduct($product);
        foreach($existingGroups as $group){
            $group->delete();
        }
        
        //add new ones.
        foreach($data['pUserGroups'] as $gID){
            self::add($product->getProductID(),$gID);
        }
    }
    
    public static function add($pID,$gID)
    {
        $productGroup = new self();
        $productGroup->setProductID($pID);
        $productGroup->getGroupID($gID);
        $productGroup->save();
        return $productGroup;
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
