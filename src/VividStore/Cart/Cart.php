<?php
namespace Concrete\Package\VividStore\src\VividStore\Cart;

use Package;
use User;
use UserInfo;
use Session;

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;

defined('C5_EXECUTE') or die(_("Access Denied."));
class Cart
{
    public function add($data)
    {
        //take our jQuery serialized data, and make it an associative array    
        $product = array();
        parse_str($data['data'],$product);
        
        $product['pID'] = (int) $product['pID'];
        $product['quantity'] = (int) $product['quantity'];
        
        //now, build a nicer "cart item"
        $cartItem = array();
        $cartItem['product'] = array(
            "pID"=>$product['pID'],
            "qty"=>$product['quantity']
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
        Session::set('cart',$cart);
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
        return Price::format($subtotal);  
    }
    public function isCustomerTaxable()
    {
        $pkg = Package::getByHandle('vivid_store');
        $pkgconfig = $pkg->getConfig();    
        $match = $pkgconfig->get('vividstore.taxMatch');
        $taxAddress = $pkgconfig->get('vividstore.taxAddress');
        $storeCity = $pkgconfig->get('vividstore.taxcity');
        $storeState = $pkgconfig->get('vividstore.taxstate');
        $storeCountry = $pkgconfig->get('vividstore.taxcountry');
        $u = new User();
        if($u->isLoggedIn()){
            $ui = UserInfo::getByID($u->getUserID());
            $customerIsTaxable = false;
            switch($taxAddress){
                case "billing":
                    $userCity = $ui->getAttribute("billing_address")->city; 
                    $userState = $ui->getAttribute("billing_address")->state_province; 
                    $userCountry = $ui->getAttribute("billing_address")->country; 
                    break;
                case "shipping":
                    $userCity = $ui->getAttribute("shipping_address")->city; 
                    $userState = $ui->getAttribute("shipping_address")->state_province; 
                    $userCountry = $ui->getAttribute("shipping_address")->country; 
                    break;
            } 
            switch($match){
                case "state":
                    if($userState==$storeState){
                        $customerIsTaxable = true;
                    }
                    break;
                case "city":
                    if($userCity==$storeCity){
                        $customerIsTaxable = true;
                    }
                    break;
                case "country":
                    if($userCountry==$storeCountry){
                        $customerIsTaxable = true;
                    }
                    break;
    
            }
        }
        return $customerIsTaxable; 
    }
    public function getTaxTotal()
    {
        $pkg = Package::getByHandle('vivid_store');
        $pkgconfig = $pkg->getConfig();
        //first check if tax is enabled in settings
        if($pkgconfig->get('vividstore.taxenabled') == "yes"){
            $cart = Session::get('cart');    
            $taxtotal = 0;
            if($cart){
                foreach ($cart as $cartItem){            
                    $pID = $cartItem['product']['pID'];
                    $qty = $cartItem['product']['qty'];
                    $product = VividProduct::getByID($pID);
                    if(is_object($product)){
                        if($product->isTaxable()){
                            //the product is "Taxable", but is the customer?
                            if(self::isCustomerTaxable()){
                                switch($pkgconfig->get('vividstore.taxBased')){
                                    case "subtotal":
                                        $productSubTotal = $product->getProductPrice() * $qty; 
                                        $taxrate = $pkgconfig->get('vividstore.taxrate') / 100;
                                        $tax = $taxrate * $productSubTotal;
                                        $taxtotal = $taxtotal + $tax;
                                        break;
                                    case "grandtotal":
                                        $productSubTotal = $product->getProductPrice() * $qty; 
                                        $shippingTotal = Price::getFloat(self::getShippingTotal());
                                        $taxableTotal = $productSubTotal + $shippingTotal;
                                        $taxrate = $pkgconfig->get('vividstore.taxrate') / 100;
                                        $tax = $taxrate * $taxableTotal;
                                        $taxtotal = $taxtotal + $tax;
                                        break;
                                }
                            }//if customer is taxable
                        }//if product is taxable
                    }//if obj
                }//foreach
            }//if cart
        }//if tax enabled
        //return self::isCustomerTaxable();
        return Price::format($taxtotal);     
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
        $pkg = Package::getByHandle('vivid_store');
        $pkgconfig = $pkg->getConfig();
        $shippingenabled = $pkgconfig->get('vividstore.shippingenabled');
        if($shippingenabled=="yes"){
            $baserate = $pkgconfig->get('vividstore.shippingbase');
            $peritemrate = $pkgconfig->get('vividstore.shippingitem');
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
        return Price::format($shippingTotal);
    }
    public function getTotal()
    {
        $subtotal = Price::getFloat(Cart::getSubTotal());
        $taxtotal = Price::getFloat(Cart::getTaxTotal()); 
        $shippingtotal = Price::getFloat(Cart::getShippingTotal());
        $grandTotal = ($subtotal + $taxtotal + $shippingtotal);
        return Price::format($grandTotal);
    }
}


