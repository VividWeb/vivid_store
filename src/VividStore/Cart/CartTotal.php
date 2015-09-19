<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Cart;

use \Concrete\Core\Controller\Controller as RouteController;

use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as StorePrice;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use \Concrete\Package\VividStore\Src\VividStore\Tax\Tax as StoreTax;

class CartTotal extends RouteController
{
        
    public function getSubTotal()
    {
        echo StorePrice::format(StoreCart::getSubTotal());
    }
    public function getTotal()
    {
        echo StorePrice::format(StoreCart::getTotal());
    }
    public function getTaxTotal()
    {
        echo json_encode(StoreTax::getTaxes(true));
    }
    public function getShippingTotal()
    {
        $smID = $_POST['smID'];
        echo StorePrice::format(StoreCart::getShippingTotal($smID));
    }
    public function getTotalItems()
    {
        echo StoreCart::getTotalItemsInCart();
    }
    
}
