<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Orders;
use Concrete\Core\Foundation\Object as Object;
use Database;
use File;
use User;
use UserInfo;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
defined('C5_EXECUTE') or die(_("Access Denied."));
class OrderItem extends Object
{
    public static function getByID($oiID) {
        $db = Database::get();
        $data = $db->GetRow("SELECT * FROM VividStoreOrderItem WHERE oiID=?",$oiID);
        if(!empty($data)){
            $item = new OrderItem();
            $item->setPropertiesFromArray($data);
        }
        return($item instanceof Item) ? $item : false;
    }  
    public function add($data,$oID,$tax=0,$taxIncluded=0,$taxName='')
    {
        $db = Database::get();
        $product = VividProduct::getByID($data['product']['pID']);
        $productName = $product->getProductName();
        $productPrice = Price::getFloat($product->getFormattedPrice());
        $qty = $data['product']['qty'];
        $inStock = $product->getProductQty();
        $newStock = $inStock - $qty;
        $product->setProductQty($newStock);
        $pID = $product->getProductID();
        $values = array($oID,$pID,$productName,$productPrice,$tax,$taxIncluded,$taxName,$qty);
        $db->Execute("INSERT INTO VividStoreOrderItem(oID,pID,oiProductName,oiPricePaid,oiTax,oiTaxIncluded,oiTaxName,oiQty) values(?,?,?,?,?,?,?,?)",$values);
        
        $oiID = $db->lastInsertId();
        
        foreach($data['productAttributes'] as $optionGroup=>$selectedOption){
            $optionGroupID = str_replace("pog","",$optionGroup);
            $optionGroupName = VividProduct::getProductOptionGroupNameByID($optionGroupID);
            $optionValue = VividProduct::getProductOptionValueByID($selectedOption);
            
            
            $values = array($oiID,$optionGroupName,$optionValue);
            $db->Execute("INSERT INTO VividStoreOrderItemOption(oiID,oioKey,oioValue) values(?,?,?)",$values);
        }
        if($product->hasDigitalDownload()){
            $fileObjs = $product->getProductDownloadFileObjects(); 
            $fileObj = $fileObjs[0];    
            $pk = \Concrete\Core\Permission\Key\FileKey::getByHandle('view_file');
            $pk->setPermissionObject($fileObj);
            $pao = $pk->getPermissionAssignmentObject();
            $u = new User();
            $uID = $u->getUserID();
            $ui = UserInfo::getByID($uID);
            $user = \Concrete\Core\Permission\Access\Entity\UserEntity::getOrCreate($ui);                    
            $pa = $pk->getPermissionAccessObject();
            if ($pa) {
                $pa->addListItem($user);
                $pao->assignPermissionAccess($pa);
            }

        }
        
    }    
    
    public function getProductName(){ return $this->oiProductName; }
    public function getPricePaid() { return Price::format($this->oiPricePaid); }
    public function getQty() { return $this->oiQty; }
    public function getSubTotal()
    {
        $price = Price::getFloat($this->getPricePaid());
        $qty = $this->getQty();
        $subtotal = $qty * $price;
        return Price::format($subtotal);
    }
    public function getProductOptions()
    {
        return Database::get()->GetAll("SELECT * FROM VividStoreOrderItemOption WHERE oiID=?",$this->oiID);
    }
    public function getProductOptionGroupNameByID($id)
    {
        $db = Database::get();
        $optionGroup = $db->GetRow("SELECT * FROM VividStoreProductOptionGroup WHERE pogID=?",$id);
        return $optionGroup['pogName'];
    }
     public function getProductOptionValueByID($id)
    {
        $db = Database::get();
        $optionItem = $db->GetRow("SELECT * FROM VividStoreProductOptionItem WHERE poiID=?",$id);
        return $optionItem['poiName'];
    }
    public function getProductObject($pID = null)
    {
        return VividProduct::getByID($this->pID);
    }
    
}