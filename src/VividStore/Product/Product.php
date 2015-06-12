<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Product;
use Concrete\Core\Foundation\Object as Object;
use Package;
use Page;
use PageType;
use PageTemplate;
use Database;
use File;
use Core;
use User;
use Config;

use Concrete\Core\Permission\Assignment\FileAssignment;
use \Concrete\Package\VividStore\Src\VividStore\Groups\ProductGroup;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
use \Concrete\Package\VividStore\Src\Attribute\Value\StoreProductValue as StoreProductValue;
use \Concrete\Package\VividStore\Src\Attribute\Key\StoreProductKey as StoreProductKey;
defined('C5_EXECUTE') or die(_("Access Denied."));

class Product extends Object
{
    
    public static function getByID($pID) 
    {
        $db = Database::get();
        $data = $db->GetRow("SELECT * FROM VividStoreProducts WHERE pID=?",$pID);
        return self::load($data);
    }  
    public static function getByCollectionID($cID)
    {
        $db = Database::get();
        $data = $db->GetRow("SELECT * FROM VividStoreProducts WHERE cID=?",$cID);
        return self::load($data);
    }
    public function load($data)
    {
        if(!empty($data)){
            $product = new Product();
            $product->setPropertiesFromArray($data);
        }
        return($product instanceof Product) ? $product : false;
    }
    public function save($data)
    {
        $db = Database::get();
        if($data['pID']){
        //if we know the pID, we're updating.
                
            $pID = $data['pID']; 
                
            //update product details
            $vals = array($data['gID'],$data['pName'],$data['pDesc'],$data['pDetail'],$data['pPrice'],$data['pFeatured'],$data['pQty'],$data['pTaxable'],$data['pfID'],$data['pActive'],$data['pShippable'],$data['pWidth'],$data['pHeight'],$data['pLength'],$data['pWeight'],$data['pID']);
            $db->Execute('UPDATE VividStoreProducts SET gID=?,pName=?,pDesc=?,pDetail=?,pPrice=?,pFeatured=?,pQty=?,pTaxable=?,pfID=?,pActive=?,pShippable=?,pWidth=?,pHeight=?,pLength=?,pWeight=? WHERE pID = ?', $vals);
            
            //update additional images
            $db->Execute('DELETE FROM VividStoreProductImages WHERE pID = ?', $data['pID']);
            $count = count($data['pifID']);
            if($count>0){
                for($i=0;$i<$count;$i++){
                    $vals = array($data['pID'],$data['pifID'][$i],$data['piSort'][$i]);
                    $db->Execute("INSERT INTO VividStoreProductImages (pID,pifID,piSort) VALUES (?,?,?)",$vals);
                }
            }

            //update user groups
            $db->Execute('DELETE FROM VividStoreProductUserGroups WHERE pID = ?', $data['pID']);
            if (!empty($data['pUserGroups'])) {
                foreach($data['pUserGroups'] as $gID){
                    $vals = array($data['pID'],$gID);
                    $db->Execute("INSERT INTO VividStoreProductUserGroups (pID,gID) VALUES (?,?)",$vals);
                }
            }
            
            //update option groups
            $db->Execute('DELETE FROM VividStoreProductOptionGroups WHERE pID = ?', $data['pID']);
            $db->Execute('DELETE FROM VividStoreProductOptionItems WHERE pID = ?', $data['pID']);
            $count = count($data['pogSort']);
            $ii=0;//set counter for items
            if($count>0){
                for($i=0;$i<$count;$i++){
                    $vals = array($data['pID'],$data['pogName'][$i],$data['pogSort'][$i]);
                    $db->Execute("INSERT INTO VividStoreProductOptionGroups (pID,pogName,pogSort) VALUES (?,?,?)",$vals);
                        //add option items
                        $pogID = $db->lastInsertId();
                        $itemsInGroup = count($data['optGroup'.$i]);
                        if($itemsInGroup>0){
                            for($gi=0;$gi<$itemsInGroup;$gi++,$ii++){
                                $vals = array($data['pID'],$pogID,$data['poiName'][$ii],$data['poiSort'][$ii]);
                                $db->Execute("INSERT INTO VividStoreProductOptionItems (pID,pogID,poiName,poiSort) VALUES (?,?,?,?)",$vals);
                            }
                        }
                }
            }
            
        } else {
        //else, we don't know it, so we're adding
            
            $dt = Core::make('helper/date');
            $now = $dt->getLocalDateTime();
            
            //add product details
            $vals = array($data['gID'],$data['pName'],$data['pDesc'],$data['pDetail'],$data['pPrice'],$data['pFeatured'],$data['pQty'],$data['pTaxable'],$data['pfID'],$data['pActive'],$data['pShippable'],$data['pWidth'],$data['pHeight'],$data['pLength'],$data['pWeight'],$now);
            $db->Execute("INSERT INTO VividStoreProducts (gID,pName,pDesc,pDetail,pPrice,pFeatured,pQty,pTaxable,pfID,pActive,pShippable,pWidth,pHeight,pLength,pWeight,pDateAdded) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",$vals);
            
            //add additional images
            $pID = $db->lastInsertId();
            $count = count($data['pifID']);
            if($count>0){
                for($i=0;$i<$count;$i++){
                    $vals = array($pID,$data['pifID'][$i],$data['piSort'][$i]);
                    $db->Execute("INSERT INTO VividStoreProductImages (pID,pifID,piSort) VALUES (?,?,?)",$vals);
                }
            }

            //update user groups
            if (!empty($data['pUserGroups'])) {
                foreach($data['pUserGroups'] as $gID){
                    $vals = array($pID,$gID);
                    $db->Execute("INSERT INTO VividStoreProductUserGroups (pID,gID) VALUES (?,?)",$vals);
                }
            }
            
            //add option groups
            $count = count($data['pogSort']);
            $ii=0;//set counter for items
            if($count>0){
                for($i=0;$i<$count;$i++){
                    $vals = array($pID,$data['pogName'][$i],$data['pogSort'][$i]);
                    $db->Execute("INSERT INTO VividStoreProductOptionGroups (pID,pogName,pogSort) VALUES (?,?,?)",$vals);
                        //add option items
                        $pogID = $db->lastInsertId();
                        $itemsInGroup = count($data['optGroup'.$i]);
                        if($itemsInGroup>0){
                            for($gi=0;$gi<$itemsInGroup;$gi++,$ii++){
                                $vals = array($pID,$pogID,$data['poiName'][$ii],$data['poiSort'][$ii]);
                                $db->Execute("INSERT INTO VividStoreProductOptionItems (pID,pogID,poiName,poiSort) VALUES (?,?,?,?)",$vals);
                            }
                        }
                }
            }
            $product = Product::getByID($pID);
            $product->generatePage();
            
        }

        //save files
        $db->Execute("DELETE FROM VividStoreDigitalFiles WHERE pID=?",$pID);
        $u = User::getByUserID(1);
        $ui = \UserInfo::getByID($u->getUserID());
        if($data['dffID']){
            foreach($data['dffID'] as $dffID){
                if($dffID){
                    $db->Execute("INSERT INTO VividStoreDigitalFiles(dffID,pID) VALUES (?,?)",array($dffID,$pID));
                    $fileObj = File::getByID($dffID);
                    $fs = \FileSet::getByName("Digital Downloads");
                    $fs->addFileToSet($fileObj);
                    $fileObj->resetPermissions(1);
                    $pk = \Concrete\Core\Permission\Key\FileKey::getByHandle('view_file');
                    $pk->setPermissionObject($fileObj);
                    $pao = $pk->getPermissionAssignmentObject();
                    $groupEntity = \Concrete\Core\Permission\Access\Entity\GroupEntity::getOrCreate(\Group::getByID(GUEST_GROUP_ID));                    
                    $pa = $pk->getPermissionAccessObject();
                    if ($pa) {
                        $pa->removeListItem($groupEntity);
                        $pao->assignPermissionAccess($pa);
                    }

                }
            }
        }
        $product = Product::getByID($pID);
        return $product;
        
        
    }
    public function remove()
    {
        $db = Database::get();
        $db->Execute("DELETE FROM VividStoreProducts WHERE pID=?",$this->pID);
        $db->Execute("DELETE FROM VividStoreProductImages WHERE pID=?",$this->pID);
        $db->Execute("DELETE FROM VividStoreProductOptionGroups WHERE pID=?",$this->pID);
        $db->Execute("DELETE FROM VividStoreProductOptionItems WHERE pID=?",$this->pID);
    }
    public function generatePage($templateID=null){
        $pkg = Package::getByHandle('vivid_store');
        $targetCID = Config::get('vividstore.productPublishTarget');
        $parentPage = Page::getByID($targetCID);
        $pageType = PageType::getByHandle('store_product');
        $pageTemplate = $pageType->getPageTypeDefaultPageTemplateObject();
        if($templateID){
            $pt = PageTemplate::getByID($templateID);
            if(is_object($pt)){
                $pageTemplate = $pt;
            }
        }
        $productParentPage = $parentPage->add(
            $pageType,
            array(
                'cName' => $this->getProductName(),
                'pkgID' => $pkg->pkgID
            ),
            $pageTemplate
        );
        $productParentPage->setAttribute('exclude_nav', 1);
        $cID = $productParentPage->getCollectionID();
        $this->setProductPageID($cID);
    }
    public function setProductPageID($cID) 
    {
        $db = Database::get();
        $vals = array($cID,$this->pID);
        $db->Execute('UPDATE VividStoreProducts SET cID=? WHERE pID = ?', $vals);
            
    }
    public function getProductID(){ return $this->pID; }
    public function getProductName(){ return $this->pName; }
    public function getProductPageID() { return $this->cID; }
    public function getProductDesc(){ return $this->pDesc; }
    public function getProductDetail() { return $this->pDetail; }
    public function getProductPrice(){ return $this->pPrice; }
    public function getFormattedPrice(){ return Price::format($this->pPrice); }
    public function isTaxable(){
        if($this->pTaxable == "1"){
            return true;
        } else {
            return false;
        }
        
    }
    public function getGroupID(){ return $this->gID; }
    public function getGroupName()
    {
        $group = ProductGroup::getByID($this->gID);
        if(is_object($group)){    
            return $group->getGroupName(); 
        }
    }
    public function isFeatured(){ return $this->pFeatured; }
    public function isActive(){ return $this->pActive; }
    public function isShippable() { return $this->pShippable; }
    public function getDimensions($whl=null){
        switch($whl){
            case "w":
                return $this->pWidth;
                break;
            case "h":
                return $this->pHeight;
                break;
            case "l":
                return $this->pLength;
                break;
            default:
                return $this->pLength."x".$this->pWidth."x".$this->pHeight;
                break;
        }
    }
    public function getProductWeight(){ return $this->pWeight; }
    public function getProductImageID() { return $this->pfID; }
    public function getProductImageObj(){
        if($this->pfID){
            $fileObj = File::getByID($this->pfID);
            return $fileObj;
        }   
    }
    public function hasDigitalDownload()
    {
        $files = $this->getProductDownloadFileIDs();
        return count($files)>0 ? true : false;
    }
    public function getProductDownloadFileIDs() 
    {
        $db = Database::get();
        $results = $db->GetAll("SELECT dffID FROM VividStoreDigitalFiles WHERE pID=?",$this->pID);
        return $results;
    }
    public function getProductDownloadFileObjects(){
        $results = $this->getProductDownloadFileIDs();
        $fileObjects = array();
        foreach($results as $result){
            $fileObjects[] = File::getByID($result['dffID']);
        }  
        return $fileObjects;
    }
    public function hasUserGroups(){
        $db = Database::get();
        $usergroupcount = $db->GetOne("SELECT COUNT(*) AS userGroupCount FROM VividStoreProductUserGroups WHERE pID=?",$this->pID);
        return ($usergroupcount > 0);
    }
    public function getProductUserGroups(){
        $db = Database::get();
        $productGroupResult = $db->GetAll("SELECT gID FROM VividStoreProductUserGroups WHERE pID=?",$this->pID);
        $productGroups = array();

        foreach($productGroupResult as $pg) {
            $productGroups[] = $pg['gID'];
        }
        return $productGroups;
    }
    public function getProductImage(){
        $fileObj = $this->getProductImageObj();
        if(is_object($fileObj)){ 
            return "<img src='".$fileObj->getRelativePath()."'>"; 
        }
    }
    public function getProductImageThumb(){
        $fileObj = $this->getProductImageObj();
        if(is_object($fileObj)){ 
            return "<img src='".$fileObj->getThumbnailURL('file_manager_listing')."'>"; 
        }
    }
    public function getProductQty(){ return $this->pQty; }
    public function setProductQty($qty)
    {  
        $db = Database::get();
        $db->Execute("UPDATE VividStoreProducts SET pQty=? WHERE pID=?",array($qty,$this->pID));
    }
    public function getProductImages()
    {
        $db = Database::get();
        $productImages = $db->GetAll("SELECT * FROM VividStoreProductImages WHERE pID=?",$this->pID);
        return $productImages;
    }
    public function getProductOptionGroups()
    {
        $db = Database::get();
        $optionGroups = $db->GetAll("SELECT * FROM VividStoreProductOptionGroups WHERE pID=? ORDER BY pogSort",$this->pID);
        return $optionGroups;
    }
    public function getProductOptionGroupNameByID($id)
    {
        $db = Database::get();
        $optionGroup = $db->GetRow("SELECT * FROM VividStoreProductOptionGroups WHERE pogID=?",$id);
        return $optionGroup['pogName'];
    }
    public function getProductOptionItems()
    {
        $db = Database::get();
        $optionItems = $db->GetAll("SELECT * FROM VividStoreProductOptionItems WHERE pID=? ORDER BY poiSort",$this->pID);
        return $optionItems;
    } 
     public function getProductOptionValueByID($id)
    {
        $db = Database::get();
        $optionItem = $db->GetRow("SELECT * FROM VividStoreProductOptionItems WHERE poiID=?",$id);
        return $optionItem['poiName'];
    }
    public function setAttribute($ak, $value)
    {
        if (!is_object($ak)) {
            $ak = StoreProductKey::getByHandle($ak);
        }
        $ak->setAttribute($this, $value);
    }
    public function getAttribute($ak, $displayMode = false) {
        if (!is_object($ak)) {
            $ak = StoreProductKey::getByHandle($ak);
        }
        if (is_object($ak)) {
            $av = $this->getAttributeValueObject($ak);
            if (is_object($av)) {
                return $av->getValue($displayMode);
            }
        }
    }
    public function getAttributeValueObject($ak, $createIfNotFound = false) {
        $db = Database::get();
        $av = false;
        $v = array($this->getProductID(), $ak->getAttributeKeyID());
        $avID = $db->GetOne("SELECT avID FROM VividStoreProductAttributeValues WHERE pID=? AND akID=?", $v);
        if ($avID > 0) {
            $av = StoreProductValue::getByID($avID);
            if (is_object($av)) {
                $av->setProduct($this);
                $av->setAttributeKey($ak);
            }
        }
        
        if ($createIfNotFound) {
            $cnt = 0;
        
            // Is this avID in use ?
            if (is_object($av)) {
                $cnt = $db->GetOne("SELECT COUNT(avID) FROM VividStoreProductAttributeValues WHERE avID=?", $av->getAttributeValueID());
            }
            
            if ((!is_object($av)) || ($cnt > 1)) {
                $av = $ak->addAttributeValue();
            }
        }
        
        return $av;
    }
}