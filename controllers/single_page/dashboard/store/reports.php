<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store;
use \Concrete\Core\Page\Controller\DashboardPageController;
use Core;
use Package;
use \Concrete\Package\VividStore\Src\VividStore\Orders\OrderStatus\OrderStatus;

use \Concrete\Package\VividStore\Src\VividStore\Orders\OrderList;
use \Concrete\Package\VividStore\Src\VividStore\Orders\Order as VividOrder;
use \Concrete\Package\VividStore\Src\VividStore\Report\Sales;

defined('C5_EXECUTE') or die("Access Denied.");
class Reports extends DashboardPageController
{

    public function view()
    {
        $this->redirect('/dashboard/store/reports/sales');
    }
    
}
