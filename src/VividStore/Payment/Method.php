<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Payment;

use Concrete\Core\Foundation\Object as Object;
use Database;
use Core;
use Package;
use Controller;
use View;

/**
 * @Entity
 * @Table(name="VividStorePaymentMethods")
 */
class Method extends Controller
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $pmID;

    /** @Column(type="text") */
    protected $pmHandle;

    /** @Column(type="text") */
    protected $pmName;

    /** @Column(type="text", nullable=true) */
    protected $pmDisplayName;

    /** @Column(type="boolean") */
    protected $pmEnabled;

    /**
     * @Column(type="integer")
     */
    protected $pkgID;

    private $methodController;

    //Property Setters/Getters
    public function setHandle($handle)
    {
        $this->pmHandle = $handle;
    }
    public function setName($name)
    {
        $this->pmName = $name;
    }
    public function setDisplayName($displayName)
    {
        $this->pmDisplayName = $displayName;
    }
    public function setEnabled($bool)
    {
        $this->pmEnabled = $bool;
    }
    public function setPackageID($id)
    {
        $this->pkgID = $id;
    }

    public function getID()
    {
        return $this->pmID;
    }
    public function getPaymentMethodID()
    {
        return $this->pmID;
    }
    public function getPaymentMethodHandle()
    {
        return $this->pmHandle;
    }
    public function getPaymentMethodName()
    {
        return $this->pmName;
    }
    public function getPaymentMethodDisplayName()
    {
        if ($this->pmDisplayName == "") {
            return $this->pmName;
        } else {
            return $this->pmDisplayName;
        }
    }
    public function isEnabled()
    {
        return $this->pmEnabled;
    }
    public function getPaymentMethodPkgID()
    {
        return $this->pkgID;
    }


    public function getMethodDirectory()
    {
        if ($this->pkgID > 0) {
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
    public function getMethodController()
    {
        return $this->methodController;
    }


    public static function getByID($pmID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();
        $method = $em->find(get_class(), $pmID);
        if ($method) {
            $method->setMethodController();
        }
        return ($method instanceof self) ? $method : false;
    }

    public static function getByHandle($pmHandle)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();
        $method = $em->getRepository(get_class())->findOneBy(array('pmHandle' => $pmHandle));
        if (is_object($method)) {
            $method->setMethodController();
        }
        return ($method instanceof self) ? $method : false;
    }

    /*
     * @param string $pmHandle
     * @param string $pmName
     * @pkg Package Object
     * @param string $pmDisplayName
     * @param bool $enabled
     */
    public static function add($pmHandle, $pmName, $pkg = null, $pmButtonLabel ='', $enabled = false)
    {
        $pm = self::getByHandle($pmHandle);
        if (!($pm instanceof self)) {
            $paymentMethod = new self();
            $paymentMethod->setHandle($pmHandle);
            $paymentMethod->setName($pmName);
            $paymentMethod->setPackageID($pkg->getPackageID());
            $paymentMethod->setDisplayName($pmName);
            $paymentMethod->setEnabled($enabled);
            $paymentMethod->save();
        }
    }

    public function save()
    {
        $em = Database::get()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = \Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }

    public static function getMethods($enabled=false)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();
        if ($enabled) {
            $methods = $em->getRepository(get_class())->findBy(array('pmEnabled'=>1));
        } else {
            $methods = $em->createQuery('select sm from '.get_class().' sm')->getResult();
        }
        foreach ($methods as $method) {
            $method->setMethodController();
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
        View::element($this->pmHandle.'/checkout_form', array('vars'=>$class->getSets()), $pkg->getPackageHandle());
    }
      
    public function renderDashboardForm()
    {
        $controller = $this->getMethodController();
        $controller->dashboardForm();
        $pkg = Package::getByID($this->pkgID);
        View::element($this->pmHandle.'/dashboard_form', array('vars'=>$controller->getSets()), $pkg->getPackageHandle());
    }
    public function renderRedirectForm()
    {
        $controller = $this->getMethodController();
        $controller->redirectForm();
        $pkg = Package::getByID($this->pkgID);
        View::element($this->pmHandle.'/redirect_form', array('vars'=>$controller->getSets()), $pkg->getPackageHandle());
    }
    
    public function submitPayment()
    {
        //load controller    
        $class = $this->getMethodController();
        return $class->submitPayment();
    }

    public function getPaymentMinimum()
    {
        return 0;
    }

    public function getPaymentMaximum()
    {
        return 1000000000; // raises pinky
    }
}
