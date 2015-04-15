<?php 
namespace Concrete\Package\VividStore\src\VividStore\Product;

use \Concrete\Core\Controller\Controller as RouteController;
use Core;
use View;
use Illuminate\Filesystem\Filesystem;

use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
defined('C5_EXECUTE') or die(_("Access Denied."));

class ProductModal extends RouteController
{
        
    public function getProductModal()
    {
        $pID = $this->post('pID');
        $product = VividProduct::getByID($pID);    
        if(Filesystem::exists(DIR_BASE."/application/elements/product_modal.php")){
            View::element("product_modal",array("product"=>$product));     
        } else {
            View::element("product_modal",array("product"=>$product),"vivid_store");    
        }
    }
    
}