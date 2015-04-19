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
class MethodType extends Controller
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
    
    public function getPaymentMethodTypeID() { return $this->smtID; }
    public function getHandle(){ return $this->smtHandle; }
    public function getName() { return $this->smtName; }
    public function getPackageID(){ return $this->pkgID; }    
    
    public static function getByID($smtID) {
        $db = Database::get();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\src\VividStore\Shipping\MethodType', $smtID);
    }  
    
    public static function getByHandle($smtHandle){
        $db = Database::get();
        $em = $db->getEntityManager();
        $shippingMethodType = $em->
            getRepository('Concrete\Package\VividStore\src\VividStore\Shipping\MethodType')->
            findOneBy(array('smtHandle' => $smtHandle));
        if (is_object($shippingMethodType)) {
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
    public function getMethodTypeDirectory()
    {
        if ($this->pkgID > 0){
            $pkg = Package::getByID($this->pkgID);
            $dir = $pkg->getPackagePath()."/src/VividStore/Shipping/Methods/".$this->smtHandle."/";
        }
        return $dir;
    }
    public function getMethodTypeController()
    {
        $th = Core::make("helper/text"); 
        $dir = $this->getMethodTypeDirectory(); 
        $file = new Filesystem();   
        $file->requireOnce($dir."controller.php");
        $namespace = "Concrete\Package\\".$th->camelcase(Package::getByID($this->pkgID)->getPackageHandle())."\src\VividStore\Shipping";
        
        $className = $th->camelcase($this->smtHandle)."ShippingMethod";
        $obj = $namespace.'\\'.$className;
        return new $obj();
    }
    public function renderDashboardForm()
    {
        $controller = $this->getMethodTypeController();
        $controller->dashboardForm();
        $pkg = Package::getByID($this->pkgID);
        View::element('shipping_method_types/'.$this->smtHandle.'/dashboard_form',array('vars'=>$controller->getSets()),$pkg->getPackageHandle());
    }
}    