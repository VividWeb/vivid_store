<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store\Settings;

use \Concrete\Core\Page\Controller\DashboardPageController;
use View;
use Loader;

use \Concrete\Package\VividStore\Src\VividStore\Shipping\MethodType as ShippingMethodType;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\Method as ShippingMethod;
defined('C5_EXECUTE') or die("Access Denied.");

class Payment extends DashboardPageController
{
    
    public function view()
    {
                       
        
    }
    public function validate($data)
    {
        $this->error = null;
        $e = Loader::helper('validation/error');
        
        return $e;
        
    }
}
