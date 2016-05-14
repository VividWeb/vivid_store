<?php
namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store;

use \Concrete\Core\Page\Controller\DashboardPageController;

class reports extends DashboardPageController
{
    public function view()
    {
        $this->redirect('/dashboard/store/reports/sales');
    }
}
