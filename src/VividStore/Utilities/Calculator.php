<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use Concrete\Package\VividStore\Src\VividStore\Tax\Tax as StoreTax;
use Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethod as StoreShippingMethod;
use Config;
use Session;

class Calculator
{
    public static function getSubTotal()
    {
        $cart = StoreCart::getCart();
        $subtotal = 0;
        if ($cart) {
            foreach ($cart as $cartItem) {
                $pID = $cartItem['product']['pID'];
                $qty = $cartItem['product']['qty'];
                $product = StoreProduct::getByID($pID);

                if ($cartItem['product']['variation']) {
                    $product->setVariation($cartItem['product']['variation']);
                }
                if (is_object($product)) {
                    $productSubTotal = $product->getActivePrice() * $qty;
                    $subtotal = $subtotal + $productSubTotal;
                }
            }
        }
        return max($subtotal, 0);
    }
    public static function getShippingTotal($smID = null)
    {
        $sessionShippingMethodID = Session::get('smID');
        if ($smID) {
            $shippingMethod = StoreShippingMethod::getByID($smID);
            Session::set('smID', $smID);
        } elseif (!empty($sessionShippingMethodID)) {
            $shippingMethod = StoreShippingMethod::getByID($sessionShippingMethodID);
        } else {
            $shippingTotal = 0;
        }
        if (is_object($shippingMethod)) {
            $shippingTotal = $shippingMethod->getShippingMethodTypeMethod()->getRate();
        } else {
            $shippingTotal = 0;
        }
        return $shippingTotal;
    }
    public static function getTaxTotals()
    {
        return StoreTax::getTaxes();
    }

    public static function getGrandTotal()
    {
        $subTotal = self::getSubTotal();
        $taxTotal = 0;
        $taxes = self::getTaxTotals();
        $taxCalc = Config::get('vividstore.calculation');
        if ($taxes && $taxCalc != 'extract') {
            foreach ($taxes as $tax) {
                $taxTotal += $tax['taxamount'];
            }
        }
        $shippingTotal = self::getShippingTotal();
        $grandTotal = ($subTotal + $taxTotal + $shippingTotal);

        return $grandTotal;
    }

    // returns an array of formatted cart totals
    public static function getTotals()
    {
        $subTotal = self::getSubTotal();
        $taxes = StoreTax::getTaxes();
        $addedTaxTotal = 0;
        $includedTaxTotal = 0;
        $taxCalc = Config::get('vividstore.calculation');

        if ($taxes) {
            foreach ($taxes as $tax) {
                if ($taxCalc != 'extract') {
                    $addedTaxTotal += $tax['taxamount'];
                } else {
                    $includedTaxTotal += $tax['taxamount'];
                }
            }
        }

        $shippingTotal = self::getShippingTotal();
        
        $total = ($subTotal + $addedTaxTotal + $shippingTotal);
        
        return array('subTotal'=>$subTotal,'taxes'=>$taxes, 'taxTotal'=>$addedTaxTotal + $includedTaxTotal, 'shippingTotal'=>$shippingTotal, 'total'=>$total);
    }

    public static function convertToMM($size)
    {
        $storeSizeUnit = Config::get('vividstore.sizeUnit');
        switch ($storeSizeUnit) {
            case "in":
                $mm = $size / 0.039370;
                break;
            case "cm":
                $mm = $size * 10;
                break;
            case "mm":
                $mm = $size;
                break;
        }
        return $mm;
    }
    public static function convertFromMM($mmSize)
    {
        $storeSizeUnit = Config::get('vividstore.sizeUnit');
        switch ($storeSizeUnit) {
            case "in":
                $size = $mmSize * 0.039370;
                break;
            case "cm":
                $size = $mmSize / 10;
                break;
            case "mm":
                $size = $mmSize;
                break;
        }
        return $size;
    }

    public static function convertToGrams($weight)
    {
        $storeWeightUnit = Config::get('vividstore.weightUnit');
        switch ($storeWeightUnit) {
            case "lb":
                $grams = $weight / 0.0022046;
                break;
            case "kg":
                $grams = $weight * 1000;
                break;
            case "g":
                $grams = $weight;
                break;
        }
        return $grams;
    }

    public static function convertFromGrams($grams)
    {
        $storeWeightUnit = Config::get('vividstore.weightUnit');
        switch ($storeWeightUnit) {
            case "lb":
                $weight = $grams * 0.0022046;
                break;
            case "kg":
                $weight = $grams / 1000;
                break;
            case "g":
                $weight = $grams;
                break;
        }
        return $weight;
    }
}
