<?php
namespace Concrete\Package\VividStore\Src\VividStore\Payment\Methods\Invoice;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as PaymentMethod;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as VividCart;
use Concrete\Package\VividStore\Src\VividStore\Utilities\Price;
use Package;
use Core;

defined('C5_EXECUTE') or die(_("Access Denied."));
class InvoicePaymentMethod extends PaymentMethod
{
    public function dashboardForm()
    {
        $this->set('form',Core::make("helper/form"));
    }
    
    public function save($data)
    {
        //nothing to save really.
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