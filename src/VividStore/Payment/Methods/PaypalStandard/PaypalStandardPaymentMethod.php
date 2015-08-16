<?php
namespace Concrete\Package\VividStore\Src\VividStore\Payment\Methods\PaypalStandard;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as PaymentMethod;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as VividCart;
use Concrete\Package\VividStore\Src\VividStore\Utilities\Price;
use Package;
use Core;

defined('C5_EXECUTE') or die(_("Access Denied."));
class PaypalStandardPaymentMethod extends PaymentMethod
{
    public $external = true;
    public function dashboardForm()
    {
        $this->set('paypalEmail',Config::get('vividstore.paypalEmail'));
        $this->set('paypalTestMode',Config::get('vividstore.paypalTestMode'));
        $this->set('form',Core::make("helper/form"));
    }
    
    public function save($data)
    {
        Config::save('vividstore.paypalEmail',$data['paypalEmail']);
        Config::save('vividstore.paypalTestMode',$data['paypalTestMode']);
    }
    public function validate($args,$e)
    {
        
        //$e->add("error message");        
        return $e;
        
    }
    public function checkoutForm()
    {
        //nada
    }
    
    public function submitPayment()
    {
        
        //nothing to do except return true
        return true;
        
    }

    
}

return __NAMESPACE__;