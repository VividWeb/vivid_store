<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Cart;

use \Concrete\Core\Controller\Controller as RouteController;
use Core;
use View;
use Session;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as VividCart;
use Illuminate\Filesystem\Filesystem;

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
defined('C5_EXECUTE') or die(_("Access Denied."));

class CartModal extends RouteController
{
        
    public function getCartModal()
    {
        $cart = Session::get('cart');
        $total = VividCart::getSubTotal();

        if(Filesystem::exists(DIR_BASE.'/application/elements/cart_modal.php')){
            View::element('cart_modal',array('cart'=>$cart,'total'=>$total));
        } else {
            View::element('cart_modal',array('cart'=>$cart,'total'=>$total),'vivid_store');
        }
    }
    
}