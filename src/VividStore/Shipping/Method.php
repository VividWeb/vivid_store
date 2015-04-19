<?php 
namespace Concrete\Package\VividStore\src\VividStore\Shipping;

use Concrete\Core\Foundation\Object as Object;
use Database;
use Core;
use Package;
use Controller;
use View;

use \Concrete\Package\VividStore\Src\VividStore\Shipping\MethodType as ShippingMethodType;

defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * @Entity
 * @Table(name="VividStoreShippingMethods")
 */
class Method 
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $smID;
    
    /**
     * @Column(type="integer")
     */
    protected $smtID;
    
    /**
     * @Column(type="integer")
     */
    protected $smtmID;
    
    /**
     * @Column(type="string")
     */
    protected $smName;
    
    /**
     * @Column(type="integer")
     */
    protected $smEnabled;
    
    public function setShippingMethodTypeID($smt){ $this->smtID = $smt->getPaymentMethodTypeID(); }
    public function setShippingMethodTypeMethodID($smtm){ $this->smtm = $smtm->getShippingMethodTypeMethodID(); }
    public function setName($name){ $this->smName = $name; }
    public function setEnabled($status){ $this->smEnabled = $status; }
    
    
    public function getShippingMethodType(){ return ShippingMethodType::getByID($this->smtID); }
    public function getName() { return $this->smName; }
    public function isEnabled(){ return $this->smEnabled; }    
    
    public static function getByID($smtID) {
        $db = Database::get();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\src\VividStore\Shipping\Method', $smID);
    }  
    
    /*
     * @smtm Shipping Method Type Method Object
     * @smt Shipping Method Type Object
     * @param string $smName
     * @param bool $smEnabled
     */
    public static function add($smtm,$smt,$smName,$smEnabled)
    {
        $sm = new self();
        $sm->setShipingMethodTypeMethodID($smtm);
        $sm->setShippingMethodTypeID($smt);
        $sm->setName($smName);
        $sm->setEnabled($smEnabled);
        $sm->save();
        return $sm;
    }
    public function save()
    {
        $em = Database::get()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    public function delete()
    {
        $em = Database::get()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}    