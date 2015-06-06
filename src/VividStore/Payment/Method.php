<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Payment;

use Concrete\Core\Foundation\Object as Object;
use Database;
use Core;
use Package;
use Controller;
use Illuminate\Filesystem\Filesystem;
use View;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Method extends Controller
{
    private $pmID;
    private $pmHandle;
    private $pmName;
    private $pkgID = 0;
    private $pmDisplayName;
    private $methodController;
      
    public function getPaymentMethodID(){ return $this->pmID; }
    public function getPaymentMethodHandle(){ return $this->pmHandle; }
    public function getPaymentMethodName(){ return $this->pmName; }
    public function getPaymentMethodPkgID(){ return $this->pkgID; }
    public function getPaymentMethodDisplayName() 
    {
        if($this->pmDisplayName == ""){
            return $this->pmName;
        } else { return $this->pmDisplayName; }
    }
    public function isEnabled(){ return $this->pmEnabled; }
    
    public static function getByID($pmID) {
        $db = Database::get();
        $data = $db->GetRow("SELECT * FROM VividStorePaymentMethods WHERE pmID=?",$pmID);
        if(!empty($data)){
            $method = new Method();
            $method->setPropertiesFromArray($data);
            $method->setMethodController();
        }
        return($method instanceof Method) ? $method : false;
    }  
    
    public static function getByHandle($pmHandle){
        $db = Database::get();
        $pm = $db->GetRow("SELECT pmID FROM VividStorePaymentMethods WHERE pmHandle=?",$pmHandle);
        return self::getByID($pm['pmID']);
    }
    
    
    public function setPropertiesFromArray($arr) {
        foreach($arr as $key => $prop) {
            $this->{$key} = $prop;
        }
    }
    public function getMethodDirectory()
    {
        if ($this->pkgID > 0){
            $pkg = Package::getByID($this->pkgID);
            $dir = $pkg->getPackagePath()."/src/VividStore/Payment/Methods/".$this->pmHandle."/";
        }
        return $dir;
    }
    
    protected function setMethodController()
    {
        $th = Core::make("helper/text");
        $namespace = "Concrete\\Package\\".$th->camelcase(Package::getByID($this->pkgID)->getPackageHandle())."\\Src\\VividStore\\Payment\\Methods\\".$th->camelcase($this->pmHandle);
        
        $className = $th->camelcase($this->pmHandle)."PaymentMethod";
        $namespace = $namespace.'\\'.$className;
        $this->methodController = new $namespace();
    }
    public function getMethodController(){ return $this->methodController; }

    /*
     * @param string $pmHandle
     * @param string $pmName
     * @pkg Package Object
     * @param string $pmDisplayName
     * @param bool $enabled
     */
    public static function add($pmHandle, $pmName, $pkg=null, $pmDisplayName=null, $enabled=false)
    {
        $db = Database::get();
        $pkgID = 0;
        if($pkg instanceof Package){
            $pkgID = $pkg->getPackageID();
        }
        if($pmDisplayName==null){
            $pmDisplayName = $pmName;
        }
        //make sure this gateway isn't already installed
        $pm = self::getByHandle($pmHandle);
        if(!($pm instanceof Method)){
            $vals = array($pmHandle,$pmName,$pmDisplayName,$pkgID);
            $db->Execute("INSERT INTO VividStorePaymentMethods (pmHandle,pmName,pmDisplayName,pkgID) VALUES (?,?,?,?)", $vals);
            $pm = self::getByHandle($pmHandle);        
            if($enabled){
                $pm->setEnabled(1);
            }
        }
        return $pm;
    }
    public function setEnabled($status)
    {
        $db = Database::get();
        $db->Execute("UPDATE VividStorePaymentMethods SET pmEnabled=? WHERE pmID=?",array($status,$this->pmID));
    }
    public function setDisplayName($name)
    {
        $db = Database::get();
        $db->Execute("UPDATE VividStorePaymentMethods SET pmDisplayName=? WHERE pmID=?",array($name,$this->pmID));
    }
      
    public function delete()
    {
        $db = Database::get();
        $db->Execute("DELETE FROM VividStorePaymentMethods WHERE pmID=?",$this->pmID);
    }
      
    public function getMethods($enabled=false)
    {
        $db = Database::get();
        if($enabled==true){
            $results = $db->GetAll("SELECT * FROM VividStorePaymentMethods WHERE pmEnabled=1");
        }else{
            $results = $db->GetAll("SELECT * FROM VividStorePaymentMethods");
        }
        $methods = array();
        foreach($results as $result){
            $method = self::getByID($result['pmID']);
            $methods[] = $method;
        }
        return $methods;
    } 
    
    public static function getEnabledMethods()
    {
        return self::getMethods(true);
    }      
              
    public function renderCheckoutForm()
    {
        $class = $this->getMethodController();
        $class->checkoutForm();
        $pkg = Package::getByID($this->pkgID);
        View::element($this->pmHandle.'/checkout_form',array('vars'=>$class->getSets()),$pkg->getPackageHandle());
    }
      
    public function renderDashboardForm()
    {
        $controller = $this->getMethodController();
        $controller->dashboardForm();
        $pkg = Package::getByID($this->pkgID);
        View::element($this->pmHandle.'/dashboard_form',array('vars'=>$controller->getSets()),$pkg->getPackageHandle());
    }
    
    public function submitPayment()
    {
        //load controller    
        $class = $this->getMethodController();
        return $class->submitPayment();
    }
      
}    
