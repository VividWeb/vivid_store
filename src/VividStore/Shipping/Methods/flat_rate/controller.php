<?php
namespace Concrete\Package\VividStore\src\VividStore\Shipping;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\MethodType as ShippingMethodType;
use Package;
use Core;
use Database;

defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * @Entity
 * @Table(name="VividStoreFlatRateMethods")
 */
class FlatRateShippingMethod extends ShippingMethodType
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $smtmID;
    
    /**
     * @Column(type="string")
     */
    protected $smID;
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
     * @Column(type="text")
     */
    protected $countriesSelected;
    
    public function setBaseRate($baseRate){ $this->baseRate = $baseRate; }
    public function setPerItemRate($perItemRate){ $this->perItemRate = $perItemRate; }
    public function setMinimumAmount($minAmount){ $this->minimumAmount = $minAmount; }
    public function setMaximumAmount($maxAmount){ $this->maximumAmount = $maxAmount; }
    public function setCountries($countries){ $this->countries = $countries; }
    public function setCountriesSelected($countriesSelected){ $this->countriesSelected; }
    
    public function getShippingMethodTypeMethodID(){ $this->smtmID; }
    public function getShippingMethodID() { return $this->smID; }
    public function getBaseRate(){ $this->baseRate; }
    public function getPerItemRate(){ $this->perItemRate; }
    public function getMinimumAmount(){ $this->minimumAmount; }
    public function getMaximumAmount(){ $this->maximumAmount; }
    public function getCountries(){ $this->countries; }
    public function getCountriesSelected(){ $this->countriesSelected; }
    
    public function dashboardForm()
    {
        $this->set('form',Core::make("helper/form"));
        $this->set('smt',ShippingMethodType::getByHandle('flat_rate'));
        $pkg = Package::getByHandle("vivid_store");
        $pkgconfig = $pkg->getConfig();
        $this->set('config',$pkgconfig);
        $this->set('countryList',Core::make('helper/lists/countries')->getCountries());
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
    }
    public function validate($args,$e)
    {
        
        //$e->add("error message");        
        return $e;
        
    }
        
}

return __NAMESPACE__;