<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store\Promotions;

use \Concrete\Core\Page\Controller\DashboardPageController;

class Manage extends DashboardPageController
{

    public function view($promotionID=null)
    {
        $this->requireAsset('css', 'vividStoreDashboard');
        $this->requireAsset('javascript', 'vividStoreFunctions');
    }

}
