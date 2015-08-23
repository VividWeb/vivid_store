<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store\Reports;
use \Concrete\Core\Page\Controller\DashboardPageController;
use Core;
use Package;
use \Concrete\Package\VividStore\Src\VividStore\Orders\OrderStatus\OrderStatus;

use \Concrete\Package\VividStore\Src\VividStore\Orders\OrderList;
use \Concrete\Package\VividStore\Src\VividStore\Orders\Order as VividOrder;
use \Concrete\Package\VividStore\Src\VividStore\Report\SalesReport;

defined('C5_EXECUTE') or die("Access Denied.");
class Sales extends DashboardPageController
{

    public function view()
    {
        $sr = new SalesReport();
		$this->set('sr',$sr);
    }
    
}
