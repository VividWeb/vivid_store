<?php
namespace Concrete\Package\VividStore\Src\VividStore\Shipping\Methods;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\MethodType as ShippingMethodType;
use Package;
use Core;

defined('C5_EXECUTE') or die(_("Access Denied."));
class FreeShippingShippingMethod extends ShippingMethodType
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
        
}
