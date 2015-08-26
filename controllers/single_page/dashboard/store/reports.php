<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store;

use \Concrete\Core\Page\Controller\DashboardPageController;

use \Concrete\Package\VividStore\Src\VividStore\Report\Sales;

defined('C5_EXECUTE') or die("Access Denied.");
class Reports extends DashboardPageController
{

    public function view()
    {
        $this->redirect('/dashboard/store/reports/sales');
    }
    
}
