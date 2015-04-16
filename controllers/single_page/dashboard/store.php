<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard;

use \Concrete\Core\Page\Controller\DashboardPageController;
use Database;

defined('C5_EXECUTE') or die("Access Denied.");
class Store extends DashboardPageController
{

    public function view(){
       $this->redirect('/dashboard/store/orders');
    }
    

}
