<?php
namespace Concrete\Package\VividStore\Src\VividStore\Shipping\Clerk;

use \DVDoug\BoxPacker\ItemList as ClerkItemList;
use \DVDoug\BoxPacker\Packer as ClerkPacker;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\Clerk\ClerkItem as StoreClerkItem;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\Clerk\ClerkPackage as StoreClerkPackage;

class ShippingClerk 
{
    /**
     * @return \DVDoug\BoxPacker\PackedBoxList
     */
    public function getPackages()
    {
        $packer = new ClerkPacker();
        $boxes = StoreClerkPackage::getPackages();
        foreach($boxes as $box){
            $packer->addBox($box);
        }
        $cartItems = StoreCart::getCart();
        foreach($cartItems as $cartItem){
            $product = StoreProduct::getByID((int)$cartitem['product']['pID']);
            $description = $product->getProductName();
            $width = $product->getDimensions('w');
            $length = $product->getDimensions('l');
            $depth = $product->getDimensions('h');
            //TODO: convert to MM if in inches format
            $weight = $product->getProductWeight();
            //TODO: convert to g if in lbs.
            $packer->addItem(new StoreClerkItem($description, $width, $length, $depth, $weight));
        }
        
        return $packer->pack();
    }
}