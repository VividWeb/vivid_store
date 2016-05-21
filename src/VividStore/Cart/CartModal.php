<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Cart;

use \Concrete\Core\Controller\Controller as RouteController;
use View;
use Illuminate\Filesystem\Filesystem;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Calculator as StoreCalculator;

class CartModal extends RouteController
{
    public function getCartModal()
    {
        $cart = StoreCart::getCart();
        $total = StoreCalculator::getSubTotal();

        if (Filesystem::exists(DIR_BASE.'/application/elements/cart_modal.php')) {
            View::element('cart_modal', array('cart'=>$cart, 'total'=>$total, 'actiondata'=>$this->post()));
        } else {
            View::element('cart_modal', array('cart'=>$cart, 'total'=>$total, 'actiondata'=>$this->post()), 'vivid_store');
        }
    }
}
