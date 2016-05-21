<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Shipping;

use Database;
use View;
use Illuminate\Filesystem\Filesystem;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethodTypeMethod as StoreShippingMethodTypeMethod;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethodType as StoreShippingMethodType;

/**
 * @Entity
 * @Table(name="VividStoreShippingMethods")
 */
class ShippingMethod
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
    
    public function setShippingMethodTypeID($smt)
    {
        $this->smtID = $smt->getShippingMethodTypeID();
    }
    public function setShippingMethodTypeMethodID($smtm)
    {
        $this->smtmID = $smtm->getShippingMethodTypeMethodID();
    }
    public function setName($name)
    {
        $this->smName = $name;
    }
    public function setEnabled($status)
    {
        $this->smEnabled = $status;
    }
    
    public function getShippingMethodID()
    {
        return $this->smID;
    }
    public function getShippingMethodType()
    {
        return StoreShippingMethodType::getByID($this->smtID);
    }
    public function getShippingMethodTypeMethod()
    {
        $methodTypeController = $this->getShippingMethodType()->getMethodTypeController();
        $methodTypeMethod = $methodTypeController->getByID($this->smtmID);
        return $methodTypeMethod;
    }
    public function getName()
    {
        return $this->smName;
    }
    public function isEnabled()
    {
        return $this->smEnabled;
    }
    
    public static function getByID($smID)
    {
        $db = Database::get();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethod', $smID);
    }
    
    public static function getAvailableMethods($methodTypeID=null)
    {
        $em = Database::get()->getEntityManager();
        if ($methodTypeID) {
            $methods = $em->getRepository('\Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethod')->findBy(array('smtID'=>$methodTypeID));
        } else {
            $methods = $em->createQuery('select sm from \Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethod sm')->getResult();
        }
        return $methods;
    }

    /**
     * @param StoreShippingMethodTypeMethod $smtm
     * @param StoreShippingMethodType $smt
     * @param string $smName
     * @param bool $smEnabled
     * @return ShippingMethod
     */
    public static function add($smtm, $smt, $smName, $smEnabled)
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
    public function update($smName, $smEnabled)
    {
        $this->setName($smName);
        $this->setEnabled($smEnabled);
        $this->save();
        return $this;
    }
    public function save()
    {
        $em = Database::get()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    public function delete()
    {
        $this->getShippingMethodTypeMethod()->delete();
        $em = Database::get()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
    public static function getEligibleMethods()
    {
        $allMethods = self::getAvailableMethods();
        $eligibleMethods = array();
        foreach ($allMethods as $method) {
            if ($method->getShippingMethodTypeMethod()->isEligible()) {
                $eligibleMethods[] = $method;
            }
        }
        return $eligibleMethods;
    }
    
    public function getShippingMethodSelector()
    {
        if (Filesystem::exists(DIR_BASE."/application/elements/checkout/shipping_methods.php")) {
            View::element("checkout/shipping_methods");
        } else {
            View::element("checkout/shipping_methods", "vivid_store");
        }
    }

    public static function getActiveShippingMethod()
    {
        $smID = \Session::get('smID');
        if ($smID) {
            $sm = self::getByID($smID);
            return $sm;
        }
    }

    public static function getActiveShippingMethodName()
    {
        $sm = self::getActiveShippingMethod();
        if ($sm instanceof ShippingMethod) {
            $shippingMethodTypeName = $sm->getShippingMethodType()->getShippingMethodTypeName();
            $shippingMethodName = $sm->getName();
            $smName = $shippingMethodTypeName.": ".$shippingMethodName;
        } else {
            $smName = t("None");
        }
        return $smName;
    }
}
