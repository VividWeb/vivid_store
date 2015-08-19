<?php
namespace Concrete\Package\VividStore\Src\VividStore\Cart;

use Package;
use User;
use UserInfo;
use Session;
use Config;
use Database;

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as Customer;

defined('C5_EXECUTE') or die(_("Access Denied."));
class Cart
{
    static protected $cart = null;

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
            $removal = false;
            // loop through and check if product hasn't been deleted. Remove from cart session if not found.
            foreach($cart as $cartitem) {
                $exists = $db->GetOne("SELECT pID FROM VividStoreProducts WHERE pID=? ",$cartitem['product']['pID']);

                if (!empty($exists)) {
                    $checkeditems[] = $cartitem;
                } else {
                    $removal = true;
                }
            }

            if ($removal) {
                Session::set('vividstore.cart', $checkeditems);
            }

            self::$cart = $checkeditems;
        }

        return self::$cart;
    }

    public function add($data)
    {
        $product = array();

        //now, build a nicer "cart item"
        $cartItem = array();
        $cartItem['product'] = array(
            "pID"=>(int) $data['pID'],
            "qty"=>(int) $data['quantity']
        );
        unset($product['pID']);
        unset($product['quantity']);
        
        //since we removed the ID/qty, we're left with just the attributes
        $cartItem['productAttributes'] = $product; 
        
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

        $exists = false;
        foreach(self::getCart() as $k=>$cart) {
            if($cart['product']['pID'] == $cartItem['product']['pID']) {
              if( count($cart['productAttributes']) == count($cartItem['productAttributes']) ) {
                if(count($cartItem['productAttributes']) === 0) {
                  $exists = $k;
                  break;
                }
                foreach($cartItem['productAttributes'] as $key=>$value) {
                  if( array_key_exists($key, $cart['productAttributes']) && $cart['productAttributes'][$key] == $value ) {
                    // Do nothing
                  } else {
                    //different attributes means different "product".
                    break 2;
                  }
                }
                $exists = $k;
              }
            }
        }
        if($exists !== false) {
            //we have a match, update the qty
            $cart = self::getCart();
            $cart[$exists]['product']['qty'] += $cartItem['product']['qty'];
            Session::set('vividstore.cart',$cart);
        }
        else {
            $cart = self::getCart();
            $cart[] = $cartItem;
            Session::set('vividstore.cart',$cart);
        }
    }
    public function update($data)
    {
        $instanceID = $data['instance'];
        $qty = $data['pQty'];
        $cart = self::getCart();

        if ($qty > 0) {
            $cart[$instanceID]['product']['qty']=$qty;
        } else {
            $this->remove($instanceID);
        }

        Session::set('vividstore.cart',$cart);
    }
    public function remove($instanceID)
    {
        $cart = self::getCart();
        unset($cart[$instanceID]);
        Session::set('vividstore.cart',$cart);
    }
    public function clear()
    {
        $cart = self::getCart();
        unset($cart);
        Session::set('vividstore.cart',null);
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
                    $productSubTotal = $product->getProductPrice() * $qty;
                    $subtotal = $subtotal + $productSubTotal;
                }
            }
        }
        return $subtotal;
    }
    public function isCustomerTaxable()
    {
        $taxAddress = Config::get('vividstore.taxAddress');
        $taxCountry = strtolower(Config::get('vividstore.taxcountry'));
        $taxState = strtolower(trim(Config::get('vividstore.taxstate')));
        $taxCity = strtolower(trim(Config::get('vividstore.taxcity')));
        $customer = new Customer;

        $customerIsTaxable = false;

        switch($taxAddress){
            case "billing":
                $userCity = strtolower(trim($customer->getValue("billing_address")->city));
                $userState = strtolower(trim($customer->getValue("billing_address")->state_province));
                $userCountry = strtolower(trim($customer->getValue("billing_address")->country));
                break;
            case "shipping":
                $userCity = strtolower(trim($customer->getValue("shipping_address")->city));
                $userState = strtolower(trim($customer->getValue("shipping_address")->state_province));
                $userCountry = strtolower(trim($customer->getValue("shipping_address")->country));
                break;
        }

        if ($userCountry == $taxCountry ) {
            $customerIsTaxable = true;
            if ($taxState && $userState != $taxState) {
                $customerIsTaxable = false;
            } elseif ($taxCity && $userCity != $taxCity) {
                $customerIsTaxable = false;
            }
        }

        return $customerIsTaxable;
    }

    public function getTaxes($formatted=false) {
        $taxTotal = self::getTaxTotal();
        $taxName = Config::get('vividstore.taxName');
        $taxCalc = Config::get('vividstore.calculation');
        $taxBased = Config::get('vividstore.taxBased');

        $taxes = array();

        if (self::isCustomerTaxable()) {
            $taxes[] = array('name'=>$taxName,'taxamount'=>$taxTotal,'calculation'=>$taxCalc, 'based'=>$taxBased);
            return $taxes;
        } 
    }


    public function getTaxTotal()
    {
        //first check if tax is enabled in settings
        if(Config::get('vividstore.taxenabled') == "yes"){
            $cart = self::getCart();
            $taxtotal = 0;
            if($cart){
                foreach ($cart as $cartItem){
                    $pID = $cartItem['product']['pID'];
                    $qty = $cartItem['product']['qty'];
                    $product = VividProduct::getByID($pID);
                    if(is_object($product)){
                        if($product->isTaxable()){
                            $taxCalc = Config::get('vividstore.calculation');

                            if ($taxCalc == 'extract') {
                                $taxrate =  10 / (Config::get('vividstore.taxrate') + 100);
                            }  else {
                                $taxrate = Config::get('vividstore.taxrate') / 100;
                            }

                            switch(Config::get('vividstore.taxBased')){
                                    case "subtotal":
                                        $productSubTotal = $product->getProductPrice() * $qty;
                                        $tax = $taxrate * $productSubTotal;
                                        $taxtotal = $taxtotal + $tax;
                                        break;
                                    case "grandtotal":
                                        $productSubTotal = $product->getProductPrice() * $qty;
                                        $shippingTotal = Price::getFloat(self::getShippingTotal());
                                        $taxableTotal = $productSubTotal + $shippingTotal;
                                        $tax = $taxrate * $taxableTotal;
                                        $taxtotal = $taxtotal + $tax;
                                        break;
                                }

                        }//if product is taxable
                    }//if obj
                }//foreach
            }//if cart
        }//if tax enabled
        //return self::isCustomerTaxable();
        return $taxtotal;
    }

    public function getTaxProduct($productID)
    {
        //first check if tax is enabled in settings
        if(Config::get('vividstore.taxenabled') == "yes"){
            $cart = self::getCart();

            if($cart){
                $taxCalc = Config::get('vividstore.calculation');

                if ($taxCalc == 'extract') {
                    $taxrate =  10 / (Config::get('vividstore.taxrate') + 100);
                }  else {
                    $taxrate = Config::get('vividstore.taxrate') / 100;
                }

                foreach ($cart as $cartItem){
                    if ($cartItem['product']['pID'] == $productID) {
                        $product = VividProduct::getByID($productID);
                    }
                    if(is_object($product)){
                        if($product->isTaxable()){
                            //the product is "Taxable", but is the customer?
                            if(self::isCustomerTaxable()){
                                    $tax = $taxrate * $product->getProductPrice() ;
                                    return $tax;

                            }//if customer is taxable
                        }//if product is taxable
                    }//if obj
                }//foreach
            }//if cart
        }//if tax enabled

        return 0;
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
        foreach(self::getCart() as $item){
            //check if items are shippable
            $product = VividProduct::getByID($item['product']['pID']);
            if ($product) {
                if($product->isShippable()){
                    return true; // return as soon as we have shippable product
                }
            }

        }
        return false;
    }

    public function getShippingTotal(){
        $shippingenabled = Config::get('vividstore.shippingenabled');
        if($shippingenabled=="yes"){
            $baserate = Config::get('vividstore.shippingbase');
            $peritemrate = Config::get('vividstore.shippingitem');
            $shippableItems = 0;
            //go through items
            if(self::getCart()){
                foreach(self::getCart() as $item){
                    //check if items are shippable
                    $product = VividProduct::getByID($item['product']['pID']);
                    if($product->isShippable()){
                        $shippableItems = $shippableItems + $item['product']['qty'];
                    }
                }
            }
            if($shippableItems > 1){
                $shippingTotal = $baserate + (($shippableItems-1) * $peritemrate);
            } elseif($shippableItems == 1) {
                $shippingTotal = $baserate;
            } elseif($shippableItems == 0){
                $shippingTotal = 0;
            }

        }
        return $shippingTotal;
    }

    public function getTotal()
    {
        $subTotal = Price::getFloat(Cart::getSubTotal());
        $taxTotal = 0;
        $taxes = self::getTaxes();

        foreach($taxes as $tax) {
            if ($tax['calculation'] != 'extract') {
                $taxTotal += $tax['taxamount'];
            }
        }

        $shippingTotal = Price::getFloat(Cart::getShippingTotal());
        $grandTotal = ($subTotal + $taxTotal + $shippingTotal);
        return $grandTotal;
    }

    // returns an array of formatted cart totals
    public function getTotals() {
        $subTotal = Price::getFloat(Cart::getSubTotal());
        $taxes = self::getTaxes();
        $addedTaxTotal = 0;
        $includedTaxTotal = 0;
        if($taxes){
            foreach($taxes as $tax) {
                if ($tax['calculation'] != 'extract') {
                    $addedTaxTotal += $tax['taxamount'];
                } else {
                    $includedTaxTotal += $tax['taxamount'];
                }
            }
        }

        $shippingTotal = Price::getFloat(Cart::getShippingTotal());
        $total = ($subTotal + $addedTaxTotal + $shippingTotal);

        return array('subTotal'=>$subTotal,'taxes'=>$taxes, 'taxTotal'=>$addedTaxTotal + $includedTaxTotal, 'shippingTotal'=>$shippingTotal, 'total'=>$total);
    }


    public function requiresLogin() {
        if(self::getCart()){
            foreach(self::getCart() as $item) {
                $product = VividProduct::getByID($item['product']['pID']);
                if ($product) {
                    if ($product->hasUserGroups() || $product->hasDigitalDownload()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}


