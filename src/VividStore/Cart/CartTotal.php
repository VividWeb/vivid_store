<?php 
namespace Concrete\Package\VividStore\src\VividStore\Cart;

use \Concrete\Core\Controller\Controller as RouteController;

use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as VividCart;
defined('C5_EXECUTE') or die(_("Access Denied."));
class CartTotal extends RouteController
{
        
    public function getSubTotal()
    {
        echo VividCart::getSubTotal();
    }
    public function getTotal()
    {
        echo VividCart::getTotal();
    }
    public function getTaxTotal()
    {
        echo VividCart::getTaxTotal();
    }
    public function getTotalItems()
    {
        echo VividCart::getTotalItemsInCart();
    }
    
}