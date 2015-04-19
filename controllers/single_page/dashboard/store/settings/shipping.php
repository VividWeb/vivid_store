<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store\Settings;
use \Concrete\Core\Page\Controller\DashboardPageController;
use Core;
use View;
use URL;
use Package;

use \Concrete\Package\VividStore\Src\VividStore\Shipping\MethodType as ShippingMethodType;

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
}
