<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Product;

use Database;

/**
 * @Entity
 * @Table(name="VividStoreProductUserGroups")
 */
class ProductUserGroup
{
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     */
    protected $pugID;
    
    /**
     * @Column(type="integer")
     */
    protected $pID;
    
    /**
     * @Column(type="integer")
     */
    protected $gID; 
    
    private function setProductID($pID){ $this->pID = $pID; }
    private function setUserGroupID($gID){ $this->gID = $gID; }
    
    public function getProductID(){ return $this->pID; }
    public function getUserGroupID() { return $this->gID; }
    
    public static function getByID($pgID) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Product\ProductGroup', $pgID);
    }
    
    public static function getUserGroupsForProduct(\Concrete\Package\VividStore\Src\VividStore\Product\Product $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\VividStore\Src\VividStore\Product\ProductUserGroup')->findBy(array('pID' => $product->getProductID()));
    }
    
    public static function addUserGroupsForProduct(array $data, \Concrete\Package\VividStore\Src\VividStore\Product\Product $product)
    {
        //clear out existing groups
        $existingUserGroups = self::getUserGroupsForProduct($product);
        foreach($existingUserGroups as $group){
            $group->delete();
        }

        //add new ones.
        if (!empty($data['pUserGroups'])) {
            foreach ($data['pUserGroups'] as $gID) {
                self::add($product->getProductID(), $gID);
            }
        }
    }
    
    public static function add($pID,$gID)
    {
        $productGroup = new self();
        $productGroup->setProductID($pID);
        $productGroup->setUserGroupID($gID);
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
