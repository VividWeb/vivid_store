<?php
namespace Concrete\Package\VividStore\src\VividStore\Shipping;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\MethodType as ShippingMethodType;
use Package;
use Core;

defined('C5_EXECUTE') or die(_("Access Denied."));
class FlatRateShippingMethod extends ShippingMethodType
{
    public function dashboardForm()
    {
        $this->set('form',Core::make("helper/form"));
        $this->set('smt',ShippingMethodType::getByHandle('flat_rate'));
        $pkg = Package::getByHandle("vivid_store");
        $pkgconfig = $pkg->getConfig();
        $this->set('config',$pkgconfig);
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

return __NAMESPACE__;