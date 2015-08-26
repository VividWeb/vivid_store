<?php
namespace Concrete\Package\VividStore\Src\VividStore\Shipping\Methods;

use Package;
use Core;
use Database;

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\MethodTypeMethod;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as VividCart;
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer;

defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * @Entity
 * @Table(name="VividStoreFlatRateMethods")
 */
class FlatRateShippingMethod extends MethodTypeMethod
{
    
    /**
     * @Column(type="decimal")
     */
    protected $baseRate;
    /**
     * @Column(type="decimal")
     */
    protected $perItemRate;
    /**
     * @Column(type="decimal")
     */
    protected $minimumAmount;
    /**
     * @Column(type="decimal")
     */
    protected $maximumAmount;
    /**
     * @Column(type="string")
     */
    protected $countries;
    /**
     * @Column(type="text",nullable=true)
     */
    protected $countriesSelected;
        
    public function setBaseRate($baseRate){ $this->baseRate = $baseRate; }
    public function setPerItemRate($perItemRate){ $this->perItemRate = $perItemRate; }
    public function setMinimumAmount($minAmount){ $this->minimumAmount = $minAmount; }
    public function setMaximumAmount($maxAmount){ $this->maximumAmount = $maxAmount; }
    public function setCountries($countries){ $this->countries = $countries; }
    public function setCountriesSelected($countriesSelected){ $this->countriesSelected = $countriesSelected; }
    
    public static function getByID($smtmID)
    {
        $em = Database::get()->getEntityManager();
        return $em->getRepository('\Concrete\Package\VividStore\Src\VividStore\Shipping\Methods\FlatRateShippingMethod')
            ->find($smtmID);
    }
    
    public function getBaseRate(){ return $this->baseRate; }
    public function getPerItemRate(){ return $this->perItemRate; }
    
    public function getMinimumAmount(){ return $this->minimumAmount; }
    public function getMaximumAmount(){ return $this->maximumAmount; }
    public function getCountries(){ return $this->countries; }
    public function getCountriesSelected(){ return $this->countriesSelected; }
    
    public function addMethodTypeMethod($data)
    {
        $sm = new self();
        $sm->setBaseRate($data['baseRate']);
        $sm->setPerItemRate($data['perItemRate']);
        $sm->setMinimumAmount($data['minimumAmount']);
        $sm->setMaximumAmount($data['maximumAmount']);
        $sm->setCountries($data['countries']);
        if($data['countriesSelected']){
            $countriesSelected = implode(',',$data['countriesSelected']);
        }
        $sm->setCountriesSelected($countriesSelected);
        
        $em = Database::get()->getEntityManager();
        $em->persist($sm);
        $em->flush();
        
        return $sm;
    }
    public function update($data)
    {
        $this->setBaseRate($data['baseRate']);
        $this->setPerItemRate($data['perItemRate']);
        $this->setMinimumAmount($data['minimumAmount']);
        $this->setMaximumAmount($data['maximumAmount']);
        if($data['countriesSelected']){
            $countriesSelected = implode(',',$data['countriesSelected']);
        }
        $this->setCountries($data['countries']);
        $this->setCountriesSelected($countriesSelected);
        
        $em = Database::get()->getEntityManager();
        $em->persist($this);
        $em->flush();
        
        return $this;
    }
    
    public function dashboardForm($shippingMethod = null)
    {
        $this->set('form',Core::make("helper/form"));
        $this->set('smt',$this);
        $pkg = Package::getByHandle("vivid_store");
        $pkgconfig = $pkg->getConfig();
        $this->set('config',$pkgconfig);
        $this->set('countryList',Core::make('helper/lists/countries')->getCountries());
        
        if(is_object($shippingMethod)){
            $smtm = $shippingMethod->getShippingMethodTypeMethod();
        } else {
            $smtm = new self();
        }
        $this->set("smtm",$smtm);
        
    }
    public function validate($args,$e)
    {
        
        if($args['baseRate']==""){
            $e->add(t("Please set a Base Rate"));
        }
        if(!is_numeric($args['baseRate'])){
            $e->add(t("Base Rate should be a number"));
        }
        if(!$args['perItemRate']==""){
            if(!is_numeric($args['perItemRate'])){
                $e->add(t("The Price Per Item doesn't have to be set, but it does have to be numeric"));
            }
        }
               
        return $e;
        
    }
    
    public function isEligible()
    {
        $customer = new Customer();
        $custCountry = $customer->getValue('shipping_address')->country;
        if($this->isWithinRange()){
            if($this->getCountries() != 'all'){
                $selectedCountries = explode(',',$this->getCountriesSelected());
                if(in_array($custCountry,$selectedCountries)){
                    return true;
                } else {
                    return false;   
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function isWithinRange()
    {
        $subtotal = VividCart::getSubTotal();
        $max = $this->getMaximumAmount();
        if($max!=0){
            if($subtotal >= $this->getMinimumAmount() && $subtotal <= $this->getMaximumAmount()){
                return true;
            } else {
                return false;
            }
        } elseif($subtotal >= $this->getMinimumAmount()) {
            return true;
        } else {
            return false;   
        }
    }    
    
    public function getRate()
    {
        $baserate = $this->getBaseRate();
        $peritemrate = $this->getPerItemRate();
        $shippableItems = VividCart::getShippableItems();
        if(count($shippableItems) > 0 ){
            $totalQty = 0;
            //go through items
            foreach($shippableItems as $item){
                //check if items are shippable
                $product = VividProduct::getByID($item['product']['pID']);
                if($product->isShippable()){
                    $totalQty = $totalQty + $item['product']['qty'];
                }
            }
            if($totalQty > 1){
                $shippingTotal = $baserate + (($totalQty-1) * $peritemrate);
            } elseif($totalQty == 1) {
                $shippingTotal = $baserate;
            }
        } else {
            $shippingTotal = 0;
        }
        return $shippingTotal;
    }
        
}
