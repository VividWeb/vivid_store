<?php 
namespace Concrete\Package\VividStore\src\VividStore\Shipping;

use Concrete\Core\Foundation\Object as Object;
use Database;
use Core;
use Package;
use Controller;
use Illuminate\Filesystem\Filesystem;
use View;

defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * @Entity
 * @Table(name="VividStoreShippingMethodTypes")
 */
class MethodType
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $smtID;
    
    /**
     * @Column(type="string")
     */
    protected $smtHandle;
    
    /**
     * @Column(type="string")
     */
    protected $smtName;
    
    /**
     * @Column(type="integer")
     */
    protected $pkgID;
    
    public function setHandle($handle)
    {
        $this->smtHandle = $handle;
    }
    public function setName($name)
    {
        $this->smtName = $name;
    }
    public function setPackageID($pkgID)
    {
        $this->pkgID = $pkgID;
    }
    
    public function getPaymentMethodTypeID() { return $this->pmtID; }
    public function getHandle(){ return $this->pmtHandle; }
    public function getName() { return $this->pmtName; }
    public function getPackageID(){ return $this->pkgID; }    
    
    public static function getByID($smtID) {
        $db = Database::get();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\src\VividStore\Shipping\MethodType', $smtID);
    }  
    
    public static function getByHandle($smtHandle){
        $db = Database::get();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\src\VividStore\Shipping\MethodType', $smtHandle);
        
        $db = Database::get();
        $em = $db->getEntityManager();
        $shippingMethodType = $em->
            getRepository('Concrete\Package\VividStore\src\VividStore\Shipping\MethodType')->
            findOneBy(array('smtHandle' => $smtHandle));
        if (is_object($shippingMethodTypebt)) {
            return $shippingMethodType;
        }
    }
    public static function add($smtHandle,$smtName,$pkg)
    {
        $smt = new self();
        $smt->setHandle($smtHandle);
        $smt->setName($smtName);
        $pkgID = $pkg->getPackageID();
        $smt->setPackageID($pkgID);
        
        $em = Database::get()->getEntityManager();
        $em->persist($smt);
        $em->flush();
    }
    public function save($smt)
    {
        $em = Database::get()->getEntityManager();
        $em->persist($smt);
        $em->flush();
        die();exit();
    }
    public function delete()
    {
        $em = Database::get()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
      
}    