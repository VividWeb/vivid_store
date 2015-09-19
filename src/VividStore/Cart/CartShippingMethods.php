<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Cart;

use \Concrete\Core\Controller\Controller;
use View;
use Illuminate\Filesystem\Filesystem;


class CartShippingMethods extends Controller
{
        
    public function getShippingMethods()
    {
        if(Filesystem::exists(DIR_BASE."/application/elements/checkout/shipping_methods.php")){
            View::element("checkout/shipping_methods");
        } else {
            View::element("checkout/shipping_methods","vivid_store");
        }
    }
    
}
