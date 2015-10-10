<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use Concrete\Package\VividStore\Src\VividStore\Product\Produc as StoreProduct;

class Calculator
{
    public function getSubTotal()
    {
        $cart = StoreCart::getCart();
        $subtotal = 0;
        if($cart){
            foreach ($cart as $cartItem){
                $pID = $cartItem['product']['pID'];
                $qty = $cartItem['product']['qty'];
                $product = StoreProduct::getByID($pID);
                if(is_object($product)){
                    $productSubTotal = $product->getActivePrice() * $qty;
                    $subtotal = $subtotal + $productSubTotal;
                }
            }
        }
        return max($subtotal,0);
    }
    public function getShippingTotal($smID = null)
    {
        $sessionShippingMethodID = Session::get('smID');
        if($smID){
            $shippingMethod = StoreShippingMethod::getByID($smID);
            Session::set('smID',$smID);
        } elseif(!empty($sessionShippingMethodID)) {
            $shippingMethod = StoreShippingMethod::getByID($sessionShippingMethodID);
        } else {
            $shippingTotal = 0;
        }
        if(is_object($shippingMethod)){
            $shippingTotal = $shippingMethod->getShippingMethodTypeMethod()->getRate();
        } else {
            $shippingTotal = 0;
        }
        return $shippingTotal;
    }
    public function getTaxTotals()
    {
        return StoreTax::getTaxes();
    }
    public function getDiscountTotals()
    {
        //should return 3 totals: subtotal, shipping, grand total
    }
    public function getGrandTotal()
    {
        $subTotal = $this->getSubTotal();
        $taxTotal = 0;
        $taxes = $this->getTaxTotals();
        $taxCalc = Config::get('vividstore.calculation');
        if($taxes && $taxCalc != 'extract'){
            foreach($taxes as $tax) {
                $taxTotal += $tax['taxamount'];
            }
        }        $shippingTotal = Cart::getShippingTotal();
        $grandTotal = ($subTotal + $taxTotal + $shippingTotal);
        
        $discounts = self::getDiscounts(); //TODO: Get total somewhere else. 
        foreach($discounts as $discount) {
            if ($discount->drDeductFrom == 'total') {
                if ($discount->drDeductType  == 'value' ) {
                    $grandTotal -= $discount->drValue;
                }

                if ($discount->drDeductType  == 'percentage' ) {
                    $grandTotal -= ($discount->drPercentage / 100 * $grandTotal);
                }
            }
        }
        
        return $grandTotal;
    }

    // returns an array of formatted cart totals
    public function getTotals() {
        $subTotal = Cart::getSubTotal();
        $taxes = StoreTax::getTaxes();
        $addedTaxTotal = 0;
        $includedTaxTotal = 0;
        $taxCalc = Config::get('vividstore.calculation');

        if($taxes){
            foreach($taxes as $tax) {
                if ($taxCalc != 'extract') {
                    $addedTaxTotal += $tax['taxamount'];
                } else {
                    $includedTaxTotal += $tax['taxamount'];
                }
            }
        }

        $shippingTotal = Cart::getShippingTotal();
        $discountedSubtotal = $subTotal;
        $discounts = self::getDiscounts();
        foreach($discounts as $discount) {
            if ($discount->drDeductFrom == 'subtotal') {
                
                if ($discount->drDeductType  == 'value' ) {
                    $discountedSubtotal -= $discount->drValue;
                }

                if ($discount->drDeductType  == 'percentage' ) {
                    $discountedSubtotal -= ($discount->drPercentage / 100 * $discountedSubtotal);
                }
            }
        }
        
        $total = ($discountedSubtotal + $addedTaxTotal + $shippingTotal);
        
        
        foreach($discounts as $discount) {
            if ($discount->drDeductFrom == 'total') {
                if ($discount->drDeductType  == 'value' ) {
                    $total -= $discount->drValue;
                }

                if ($discount->drDeductType  == 'percentage' ) {
                    $total -= ($discount->drPercentage / 100 * $total);
                }
            }
        }

        return array('subTotal'=>$subTotal,'taxes'=>$taxes, 'taxTotal'=>$addedTaxTotal + $includedTaxTotal, 'shippingTotal'=>$shippingTotal, 'total'=>$total);
    }
}
