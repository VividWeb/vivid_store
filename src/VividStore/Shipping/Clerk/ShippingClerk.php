<?php
namespace Concrete\Package\VividStore\Src\VividStore\Shipping\Clerk;

use \DVDoug\BoxPacker\Packer as ClerkPacker;
use Controller;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\Clerk\ClerkItem as StoreClerkItem;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\Clerk\ClerkPackage as StoreClerkPackage;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Calculator as StoreCalculator;

class ShippingClerk extends Controller
{
    /**
     * @return \DVDoug\BoxPacker\PackedBoxList
     */
    public function getPackages()
    {
        $packer = new ClerkPacker();
        $boxes = StoreClerkPackage::getPackages();
        foreach ($boxes as $box) {
            $packer->addBox($box);
        }
        $cartItems = StoreCart::getCart();
        foreach ($cartItems as $cartItem) {
            $product = StoreProduct::getByID((int)$cartItem['product']['pID']);
            $description = $product->getProductName();
            $width = StoreCalculator::convertToMM($product->getDimensions('w'));
            $length = StoreCalculator::convertToMM($product->getDimensions('l'));
            $depth = StoreCalculator::convertToMM($product->getDimensions('h'));
            $weight = StoreCalculator::convertToGrams($product->getProductWeight());
            $packer->addItem(new StoreClerkItem($description, $width, $length, $depth, $weight));
            //TODO: If an item doesn't fit in any box, make it it's own box.
        }
                
        try {
            $packages = $packer->pack();
        } catch (Exception $e) {
            var_dump($e);
        }
    }
    public static function test()
    {
        echo "<pre>";
        var_dump(self::getPackages());
        echo "</pre>";
    }
}
