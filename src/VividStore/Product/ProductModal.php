<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Product;

use \Concrete\Core\Controller\Controller;
use View;
use Illuminate\Filesystem\Filesystem;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;

class ProductModal extends Controller
{
    public function getProductModal()
    {
        $pID = $this->post('pID');
        $product = StoreProduct::getByID($pID);
        if (Filesystem::exists(DIR_BASE."/application/elements/product_modal.php")) {
            View::element("product_modal", array("product"=>$product));
        } else {
            View::element("product_modal", array("product"=>$product), "vivid_store");
        }
    }
}
