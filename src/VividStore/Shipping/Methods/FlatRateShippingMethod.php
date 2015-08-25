<?php
namespace Concrete\Package\VividStore\Src\VividStore\Shipping\Methods;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\MethodTypeMethod;
use Package;
use Core;
use Database;

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
        
	public function setBaseRate($baseRate){ $this->baseRate = $baseRate; }
    public function setPerItemRate($perItemRate){ $this->perItemRate = $perItemRate; }
	
	public static function getByID($smtmID)
    {
        $em = Database::get()->getEntityManager();
        return $em->getRepository('\Concrete\Package\VividStore\Src\VividStore\Shipping\Methods\FlatRateShippingMethod')
            ->find($smtmID);
    }
    
    public function getBaseRate(){ return $this->baseRate; }
    public function getPerItemRate(){ return $this->perItemRate; }
    
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
    public function addMethodTypeMethod($data)
    {
        $sm = new self();
        $sm->setBaseRate($data['baseRate']);
        $sm->setPerItemRate($data['perItemRate']);
        $sm->setMinimumAmount($data['minimumAmount']);
        $sm->setMaximumAmount($data['maximumAmount']);
        $sm->setCountries($data['countries']);
        $sm->setCountriesSelected($data['countriesSelected']);
        
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
        $this->setCountries($data['countries']);
        $this->setCountriesSelected($data['countriesSelected']);
        
        $em = Database::get()->getEntityManager();
        $em->persist($this);
        $em->flush();
		
		return $this;
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
        
}
