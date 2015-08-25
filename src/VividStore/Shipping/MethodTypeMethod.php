<?php
namespace Concrete\Package\VividStore\Src\VividStore\Shipping;

use Database;
use Controller;


defined('C5_EXECUTE') or die(_("Access Denied."));

abstract class MethodTypeMethod extends Controller
{
    /**
     * @Id 
     * @Column(name="smtmID",type="integer",nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $smtmID;
    
    /**
     * @Column(type="string",nullable=true)
     */
    protected $smID;
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
    
    public function setShippingMethodID($smID){ $this->smID = $smID; }
    public function setMinimumAmount($minAmount){ $this->minimumAmount = $minAmount; }
    public function setMaximumAmount($maxAmount){ $this->maximumAmount = $maxAmount; }
    public function setCountries($countries){ $this->countries = $countries; }
    public function setCountriesSelected($countriesSelected){ $this->countriesSelected; }
    
    abstract public static function getByID($smtmID);
    
    public function getShippingMethodTypeMethodID(){ return $this->smtmID; }
    public function getShippingMethodID() { return $this->smID; }
    public function getMinimumAmount(){ return $this->minimumAmount; }
    public function getMaximumAmount(){ return $this->maximumAmount; }
    public function getCountries(){ return $this->countries; }
    public function getCountriesSelected(){ return $this->countriesSelected; }
    
    abstract public function dashboardForm();
    abstract public function addMethodTypeMethod($data);
    abstract public function update($data);
    public function save()
    {
        $em = Database::get()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    
}
