<?php
namespace Concrete\Package\VividStore\Src\VividStore\Cart;

use Package;
use User;
use UserInfo;
use Session;
use Config;

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as Customer;

defined('C5_EXECUTE') or die(_("Access Denied."));
class Cart
{
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
         
        if(!is_array(Session::get('cart'))) {
            Session::set('cart',array());
        }
        $exists = false;
        foreach(Session::get('cart') as $k=>$cart) {
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
            $cart = Session::get('cart');
            $cart[$exists]['product']['qty'] += $cartItem['product']['qty'];
            Session::set('cart',$cart);
        }
        else {
            $cart = Session::get('cart');
            $cart[] = $cartItem;    
            Session::set('cart',$cart);
        }
    }
    public function update($data)
    {
        $instanceID = $data['instance'];
        $qty = $data['pQty'];        
        $cart = Session::get('cart');
        $cart[$instanceID]['product']['qty']=$qty;
        Session::set('cart',$cart);
    }
    public function remove($instanceID)
    {
        $cart = Session::get('cart');
        unset($cart[$instanceID]);
        Session::set('cart',$cart);
    }
    public function clear()
    {
        $cart = Session::get('cart');
        unset($cart);
        Session::set('cart',null);
    }
    public function getSubTotal()
    {
        $cart = Session::get('cart');    
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
        }

        return $taxes;
    }


    public function getTaxTotal()
    {
        //first check if tax is enabled in settings
        if(Config::get('vividstore.taxenabled') == "yes"){
            $cart = Session::get('cart');    
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
            $cart = Session::get('cart');

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
        if(Session::get('cart')){
            foreach(Session::get('cart') as $item){
                $subtotal = $item['product']['qty'];
                $total = $total + $subtotal;
            }
        }
        return $total;
    }
    public function getShippingTotal(){
        $shippingenabled = Config::get('vividstore.shippingenabled');
        if($shippingenabled=="yes"){
            $baserate = Config::get('vividstore.shippingbase');
            $peritemrate = Config::get('vividstore.shippingitem');
            $shippableItems = 0;
            //go through items
            if(Session::get('cart')){
                foreach(Session::get('cart') as $item){
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

        foreach($taxes as $tax) {
            if ($tax['calculation'] != 'extract') {
                $addedTaxTotal += $tax['taxamount'];
            } else {
                $includedTaxTotal += $tax['taxamount'];
            }
        }

        $shippingTotal = Price::getFloat(Cart::getShippingTotal());
        $total = ($subTotal + $addedTaxTotal + $shippingTotal);

        return array('subTotal'=>$subTotal,'taxes'=>$taxes, 'taxTotal'=>$addedTaxTotal + $includedTaxTotal, 'shippingTotal'=>$shippingTotal, 'total'=>$total);
    }


    public function requiresLogin() {
        if(Session::get('cart')){
            foreach(Session::get('cart') as $item) {
                $product = VividProduct::getByID($item['product']['pID']);
                if ($product->hasUserGroups() || $product->hasDigitalDownload()) {
                    return true;
                }
            }
        }

        return false;
    }
}


