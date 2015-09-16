<?php
namespace Concrete\Package\VividStore\Src\VividStore\Cart;

use Session;
use Config;
use Database;

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\Method as ShippingMethod;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as Customer;
use \Concrete\Package\VividStore\Src\VividStore\Discount\DiscountRule as DiscountRule;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as Product;
use \Concrete\Package\VividStore\Src\VividStore\Tax\Tax;

defined('C5_EXECUTE') or die(_("Access Denied."));
class Cart
{
    static protected $cart = null;
    static protected $discounts = null;

    public static function getCart() {

        // this acts as a singleton, in that it wil only fetch the cart form the session and check it for validity once per request
        if (!isset(self::$cart)) {
            $cart = Session::get('vividstore.cart');
            if(!is_array($cart)) {
               Session::set('vividstore.cart',array());
               $cart = array();
            }

            $db = Database::get();

            $checkeditems = array();
            $update = false;
            // loop through and check if product hasn't been deleted. Remove from cart session if not found.
            foreach($cart as $cartitem) {
                $product =Product::getByID((int)$cartitem['product']['pID']);

                if ($product) {
                    // check that we dont have a non-quantity product in cart with a quantity > 1
                    if (!$product->allowQuantity() && $cartitem['product']['qty'] > 0) {
                        $cartitem['product']['qty'] = 1;
                        $update = true;
                    }

                    $checkeditems[] = $cartitem;
                } else {
                    $update = true;
                }
            }

            if ($update) {
                Session::set('vividstore.cart', $checkeditems);
            }

            self::$discounts = array();

            $rules = DiscountRule::findAutomaticDiscounts();
            if (count($rules) > 0) {
                self::$discounts = array_merge(self::$discounts, $rules);
            }

            $code = trim(Session::get('vividstore.code'));
            if ($code) {
                $rules = DiscountRule::findDiscountRuleByCode($code);

                if (count($rules) > 0) {
                    self::$discounts = array_merge(self::$discounts, $rules);
                } else {
                    Session::set('vividstore.code', '');
                }
            }

            self::$cart = $checkeditems;
        }

        return self::$cart;
    }

    public static function getDiscounts() {
        if (!isset(self::$cart)) {
            self::getCart();
        }

        return self::$discounts;
    }

    public function add($data)
    {

        $product = Product::getByID((int)$data['pID']);

        if (!$product) {
            return false;
        }

        if ($product->isExclusive()) {
            self::clear();
        }

        //now, build a nicer "cart item"
        $cartItem = array();
        $cartItem['product'] = array(
            "pID"=>(int) $data['pID'],
            "qty"=>(int) $data['quantity']
        );
        unset($data['pID']);
        unset($data['quantity']);

        //since we removed the ID/qty, we're left with just the attributes
        $cartItem['productAttributes'] = $data;

        /*
         * We need to add the item to the cart, however, first we need to do some comparisons.
         * If we're adding a product that already exists, but the attributes are different,
         * we need to create a new instance of that item. 
         * If the attibutes are the same, we just need to update the quantity.
         * If it doesn't exist, we're free to just add it. 
         * 
         * phew.
         * 
         */

        $added = 0;
        $existingproductcount = 0;

        $exists = false;
        foreach(self::getCart() as $k=>$cart) {
            if($cart['product']['pID'] == $cartItem['product']['pID']) {
              if( count($cart['productAttributes']) == count($cartItem['productAttributes']) ) {
                if(count($cartItem['productAttributes']) === 0) {
                  $sameproduct = true;
                  break;
                }
                foreach($cartItem['productAttributes'] as $key=>$value) {
                  if( array_key_exists($key, $cart['productAttributes']) && $cart['productAttributes'][$key] == $value ) {
                      $sameproduct = true;
                  } else {
                    //different attributes means different "product".
                    $sameproduct = false;
                    break;
                  }
                }
              }
            }
        }

        if ($sameproduct) {
            $exists = $k;
        }

        $removeexistingexclusive  = false;

        foreach(self::getCart() as $k=>$cart) {
            $cartproduct = Product::getByID((int)$cart['product']['pID']);

            if ($cartproduct && $cartproduct->isExclusive()) {
                self::remove($k);
                $removeexistingexclusive = true;
            }
        }

        $cart = self::getCart();

        if ($exists !== false) {
            $existingproductcount = $cart[$exists]['product']['qty'];

            //we have a match, update the qty
            if ($product->allowQuantity()) {
                $newquantity = $cart[$exists]['product']['qty'] + $cartItem['product']['qty'];

                if (!$product->isUnlimited() &&  !$product->allowBackOrders() && $product->getProductQty() < max($newquantity, $existingproductcount)) {
                    $newquantity = $product->getProductQty();
                }

                $added = $newquantity - $existingproductcount;

            } else {
                $added = 1;
                $newquantity = 1;
            }

            $cart[$exists]['product']['qty'] = $newquantity;
        } else {
            $newquantity = $cartItem['product']['qty'];


            if (!$product->isUnlimited() && !$product->allowBackOrders() && $product->getProductQty() < $newquantity) {
                $newquantity = $product->getProductQty();
            }

            $cart[$exists]['product']['qty'] = $newquantity;

            if ($product->isExclusive()) {
                $cart = array($cartItem);
            } else {
                $cart[] = $cartItem;
            }

            $added = $newquantity;

        }


        Session::set('vividstore.cart', $cart);

        return array('added' => $added, 'exclusive'=>$product->isExclusive(), 'removeexistingexclusive'=> $removeexistingexclusive);
    }

    public function update($data)
    {
        $instanceID = $data['instance'];
        $qty = $data['pQty'];
        $cart = self::getCart();

        $product = Product::getByID((int)$cart[$instanceID]['product']['pID']);

        if ($qty > 0 && $product) {
            $newquantity = $qty;

            if (!$product->isUnlimited() && !$product->allowBackOrders() && $product->getProductQty() < $newquantity) {
                $newquantity = $product->getProductQty();
            }

            $cart[$instanceID]['product']['qty'] = $newquantity;
            $added = $newquantity;
        } else {
            self::remove($instanceID);
        }

        Session::set('vividstore.cart', $cart);
        self::$cart = null;

        return array('added' => $added);
    }

    public function remove($instanceID)
    {
        $cart = self::getCart();
        unset($cart[$instanceID]);
        Session::set('vividstore.cart',$cart);
        self::$cart = null;
    }

    public static function clear()
    {
        $cart = self::getCart();
        unset($cart);
        Session::set('vividstore.cart', null);
        self::$cart = null;
    }
    public function getSubTotal()
    {
        $cart = self::getCart();
        $subtotal = 0;
        if($cart){
            foreach ($cart as $cartItem){
                $pID = $cartItem['product']['pID'];
                $qty = $cartItem['product']['qty'];
                $product = VividProduct::getByID($pID);
                if(is_object($product)){
                    $productSubTotal = $product->getActivePrice() * $qty;
                    $subtotal = $subtotal + $productSubTotal;
                }
            }
        }

        $discounts = self::getDiscounts();
        /*
        foreach($discounts as $discount) {
            if ($discount->drDeductFrom == 'subtotal') {
                if ($discount->drDeductType  == 'value' ) {
                    $subtotal -= $discount->drValue;
                }

                if ($discount->drDeductType  == 'percentage' ) {
                    $subtotal -= ($discount->drPercentage / 100 * $subtotal);
                }
            }
        }
        */
        return max($subtotal,0);
        
    }

    public function getTotalItemsInCart(){
        $total = 0;
        if(self::getCart()){
            foreach(self::getCart() as $item){
                $subtotal = $item['product']['qty'];
                $total = $total + $subtotal;
            }
        }
        return $total;
    }

    public function isShippable() {
        $shippableItems = self::getShippableItems();
        $shippingMethods = ShippingMethod::getAvailableMethods();
        if(count($shippingMethods) > 0){
            if(count($shippableItems) > 0){
                return true;
            } else{
                return false;
            }
        } else {
           return false;
        }
    }

    public function getShippableItems()
    {

        $shippableItems = array();
        //go through items
        if (self::getCart()) {
            foreach (self::getCart() as $item) {
                //check if items are shippable
                $product = VividProduct::getByID($item['product']['pID']);
                if ($product->isShippable()) {
                    $shippableItems[] = $item;
                }
            }
        }

        return $shippableItems;
    }
    
    public function getCartWeight()
    {
        $totalWeight = 0;
        if(self::getCart()){
            foreach(self::getCart() as $item){
                $product = VividProduct::getByID($item['product']['pID']);
                if($product->isShippable()){
                    $totalProductWeight = $product->getProductWeight() * $item['product']['qty'];
                    $totalWeight = $totalWeight + $totalProductWeight;
                }      
            }
        }
        //only returns weight of shippable items.
        return $totalWeight;
    }

    public function getShippingTotal($smID=null){
        
        if($smID){
            $shippingMethod = ShippingMethod::getByID($smID);
            Session::set('smID',$smID);
        } else {
            $sessionShippingMethodID = Session::get('smID');
            if(!empty($sessionShippingMethodID)){
                $shippingMethod = ShippingMethod::getByID($sessionShippingMethodID);
            }
        }
        if(is_object($shippingMethod)){
            $shippingTotal = $shippingMethod->getShippingMethodTypeMethod()->getRate();
            
            $discounts = self::getDiscounts();
    
            foreach($discounts as $discount) {
                if ($discount->drDeductFrom == 'shipping') {
                    if ($discount->drDeductType  == 'value' ) {
                        $shippingTotal -= $discount->drValue;
                    }
    
                    if ($discount->drDeductType  == 'percentage' ) {
                        $shippingTotal -= ($discount->drPercentage / 100 * $shippingTotal);
                    }
                }
            }
        }
        return $shippingTotal;
    }

    public function getTotal()
    {
        $subTotal = Price::getFloat(Cart::getSubTotal());
        $taxTotal = 0;
        $taxes = Tax::getTaxes();
        $taxCalc = Config::get('vividstore.calculation');

        if($taxes && $taxCalc != 'extract'){
            foreach($taxes as $tax) {
                $taxTotal += $tax['taxamount'];
            }
        }

        
        $shippingTotal = Cart::getShippingTotal();
        $grandTotal = ($subTotal + $taxTotal + $shippingTotal);
        
        $discounts = self::getDiscounts();
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
        $taxes = Tax::getTaxes();
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

    // determines if a cart requires a customer to be logged in
    public function requiresLogin() {
        if(self::getCart()){
            foreach(self::getCart() as $item) {
                $product = VividProduct::getByID($item['product']['pID']);
                if ($product) {
                    if (($product->hasUserGroups() || $product->hasDigitalDownload()) && !$product->createsLogin()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    // determines if the cart contains a product that will auto-create a user account
    public function createsAccount() {
        if(self::getCart()){
            foreach(self::getCart() as $item) {
                $product = VividProduct::getByID($item['product']['pID']);
                if ($product) {
                    if ($product->createsLogin()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function storeCode($code) {
        $rule = DiscountRule::findDiscountRuleByCode($code);

        if (!empty($rule)) {
            Session::set('vividstore.code',$code);
            return true;
        }

        return false;
    }

    public static function hasCode() {
        return (bool)Session::get('vividstore.code');
    }

    public static function getCode() {
        return Session::get('vividstore.code');
    }

    public static function clearCode() {
        Session::set('vividstore.code', '');
    }
}
