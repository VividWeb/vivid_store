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
		$pkg = Package::getByHandle('vivid_store');
        $packagePath = $pkg->getRelativePath();
		$this->addHeaderItem(Core::make('helper/html')->css($packagePath.'/css/chartist.css'));
        $this->addFooterItem(Core::make('helper/html')->javascript($packagePath.'/js/chartist.js'));
    }
    
}
