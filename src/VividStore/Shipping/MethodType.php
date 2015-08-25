<?php 
namespace Concrete\Package\VividStore\src\VividStore\Shipping;

use Database;
use Core;
use Package;
use View;

defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * @Entity
 * @Table(name="VividStoreShippingMethodTypes")
 */
class MethodType
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
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
    
    private $methodTypeController;
    
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
    public function setMethodTypeController()
    {
        $th = Core::make("helper/text");
        $namespace = "Concrete\\Package\\".$th->camelcase(Package::getByID($this->pkgID)->getPackageHandle())."\\Src\\VividStore\\Shipping\\Methods";
        
        $className = $th->camelcase($this->smtHandle)."ShippingMethod";
        $obj = $namespace.'\\'.$className;
        $this->methodTypeController = new $obj();
    }
    
    public function getShippingMethodTypeID() { return $this->smtID; }
    public function getHandle(){ return $this->smtHandle; }
    public function getShippingMethodTypeName() { return $this->smtName; }
    public function getPackageID(){ return $this->pkgID; }
    public function getMethodTypeController(){ return $this->methodTypeController; }
    
    public static function getByID($smtID) {
        $db = Database::get();
        $em = $db->getEntityManager();
        $obj = $em->find('Concrete\Package\VividStore\src\VividStore\Shipping\MethodType', $smtID);
        $obj->setMethodTypeController();
        return $obj;
    }
    
    public static function getByHandle($smtHandle){
        $db = Database::get();
        $em = $db->getEntityManager();
        $obj = $em->
            getRepository('Concrete\Package\VividStore\src\VividStore\Shipping\MethodType')->
            findOneBy(array('smtHandle' => $smtHandle));
        if (is_object($obj)) {
            $obj->setMethodTypeController();
            return $obj;
        }
    }
    public static function add($smtHandle,$smtName,$pkg)
    {
        $smt = new self();
        $smt->setHandle($smtHandle);
        $smt->setName($smtName);
        $pkgID = $pkg->getPackageID();
        $smt->setPackageID($pkgID);
        $smt->save();
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
    public static function getAvailableMethodTypes()
    {
        $db = Database::get();
        $data = $db->GetAll("SELECT * FROM VividStoreShippingMethodTypes");
        $methodTypes = array();
        foreach($data as $result){
            $methodTypes[] = self::getByID($result['smtID']);
        }
        return $methodTypes;
    }
    public function renderDashboardForm($sm)
    {
        $controller = $this->getMethodTypeController();
        $controller->dashboardForm($sm);
        $pkg = Package::getByID($this->pkgID);
        View::element('shipping_method_types/'.$this->smtHandle.'/dashboard_form',array('vars'=>$controller->getSets()),$pkg->getPackageHandle());
    }
    public function addMethod($data)
    {
        $sm = $this->getMethodTypeController()->addMethodTypeMethod($data);
        return $sm;
    }
}
