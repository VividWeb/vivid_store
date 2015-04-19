<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store\Settings;
use \Concrete\Core\Page\Controller\DashboardPageController;
use Core;
use View;
use URL;
use Package;

use \Concrete\Package\VividStore\Src\VividStore\Shipping\MethodType as ShippingMethodType;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\Method as ShippingMethod;

defined('C5_EXECUTE') or die("Access Denied.");

class Shipping extends DashboardPageController
{
	
	public function view() 
	{
		$this->set("methodTypes",ShippingMethodType::getAvailableMethodTypes());
	}
    public function add($smtID)
    {
        $this->set('pageTitle',t("Add Shipping Method"));
        $smt = ShippingMethodType::getByID($smtID);
        $this->set('smt',$smt);
    }
    public function add_method()
    {
        //first we send the data to the shipping method type.
        $shippingMethodType = ShippingMethodType::getByID($this->post('shippingMethodTypeID'));
        $shippingMethodTypeMethod = $shippingMethodType->addMethod($this->post());
        
        //if that was error free, we made a shipping method that correlates with it.
        ShippingMethod::add($shippingMethodTypeMethod,$shippingMethodType,$this->post('methodName'),$this->post('methodEnabled'));
    }
    public function validate($data)
    {
        
    }
}
