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
    
    
    public function setShippingMethodID($smID){ $this->smID = $smID; }
    
    abstract public static function getByID($smtmID);
    
    public function getShippingMethodTypeMethodID(){ return $this->smtmID; }
    public function getShippingMethodID() { return $this->smID; }
    
    abstract public function dashboardForm();
    abstract public function addMethodTypeMethod($data);
    abstract public function update($data);
    abstract public function isEligible();
    abstract public function getRate();
    
    public function save()
    {
        $em = Database::get()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    
}
