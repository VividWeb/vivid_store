<?php 
namespace Concrete\Package\VividStore\src\VividStore\Shipping;

use Concrete\Core\Foundation\Object as Object;
use Database;

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
    
    public function setShippingMethodTypeID($smt){ $this->smtID = $smt->getShippingMethodTypeID(); }
    public function setShippingMethodTypeMethodID($smtm){ $this->smtmID = $smtm->getShippingMethodTypeMethodID(); }
    public function setName($name){ $this->smName = $name; }
    public function setEnabled($status){ $this->smEnabled = $status; }
    
    public function getShippingMethodID(){ return $this->smID; }
    public function getShippingMethodType(){ return ShippingMethodType::getByID($this->smtID); }
    public function getShippingMethodTypeMethod(){
        $methodTypeController = $this->getShippingMethodType()->getMethodTypeController();
        $methodTypeMethod = $methodTypeController->getByID($this->smtmID);
        return $methodTypeMethod;
    }
    public function getName() { return $this->smName; }
    public function isEnabled(){ return $this->smEnabled; }
    
    public static function getByID($smID) {
        $db = Database::get();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\src\VividStore\Shipping\Method', $smID);
    }
    
    public static function getAvailableMethods($methodTypeID=null)
    {
        $em = Database::get()->getEntityManager();
        if($methodTypeID){
            $methods = $em->getRepository('\Concrete\Package\VividStore\src\VividStore\Shipping\Method')->findBy(array('smtID'=>$methodTypeID));
        } else {
            $methods = $em->createQuery('select u from \Concrete\Package\VividStore\src\VividStore\Shipping\Method u')->getResult();
        }
        return $methods;
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
        $sm->setShippingMethodTypeMethodID($smtm);
        $sm->setShippingMethodTypeID($smt);
        $sm->setName($smName);
        $sm->setEnabled($smEnabled);
        $sm->save();
        $smtm->setShippingMethodID($sm->getShippingMethodID());
        $smtm->save();
        return $sm;
    }
    public function update($smName,$smEnabled)
    {
        $this->setName($smName);
        $this->setEnabled($smEnabled);
        $this->save();
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
    public static function getEligibleMethods()
    {
        $allMethods = self::getAvailableMethods();
        $eligibleMethods = array();
        $i=1;
        foreach($allMethods as $method){
            if($method->getShippingMethodTypeMethod()->isEligible()){
                $eligibleMethods[] = $method;
            }
            $i++;
        }
        return $eligibleMethods;
    }
}
