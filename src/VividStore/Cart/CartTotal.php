<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Cart;

use \Concrete\Core\Controller\Controller as RouteController;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as StorePrice;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use \Concrete\Package\VividStore\Src\VividStore\Tax\Tax as StoreTax;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Calculator as StoreCalculator;

class CartTotal extends RouteController
{
    public function getSubTotal()
    {
        echo StorePrice::format(StoreCalculator::getSubTotal());
    }
    public function getTotal()
    {
        echo StorePrice::format(StoreCalculator::getGrandTotal());
    }
    public function getTaxTotal()
    {
        echo json_encode(StoreTax::getTaxes(true));
    }
    public function getShippingTotal()
    {
        $smID = $_POST['smID'];
        echo StorePrice::format(StoreCalculator::getShippingTotal($smID));
    }
    public function getTotalItems()
    {
        echo StoreCart::getTotalItemsInCart();
    }
}
