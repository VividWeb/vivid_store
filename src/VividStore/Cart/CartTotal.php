<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Cart;

use \Concrete\Core\Controller\Controller as RouteController;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;

use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as VividCart;
defined('C5_EXECUTE') or die(_("Access Denied."));
class CartTotal extends RouteController
{
        
    public function getSubTotal()
    {
        echo Price::format(VividCart::getSubTotal());
    }
    public function getTotal()
    {
        echo Price::format(VividCart::getTotal());
    }
    public function getTaxTotal()
    {
        echo json_encode(VividCart::getTaxes(true));
    }
    public function getTotalItems()
    {
        echo VividCart::getTotalItemsInCart();
    }
    
}
