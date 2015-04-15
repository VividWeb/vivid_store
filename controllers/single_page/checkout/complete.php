<?php
namespace Concrete\Package\VividStore\Controller\SinglePage\Checkout;
use Page;
use PageController;
use Core;
use \Concrete\Core\Localization\Service\CountryList;
use View;
use Package;
use User;

use Concrete\Package\VividStore\Src\VividStore\Orders\Order as VividOrder;

defined('C5_EXECUTE') or die(_("Access Denied."));
class Complete extends PageController
{
    public function view()
    {
        $u = new User();
        $uID = $u->getUserID();
        $order = VividOrder::getCustomersMostRecentOrderByCID($uID);
        if(is_object($order)){
            $this->set("order",$order);
        } else {
            $this->redirect("/cart");
        }
        $this->addFooterItem(Core::make('helper/html')->javascript($packagePath.'/js/vivid-store.js','vivid-store'));   
        $this->addHeaderItem(Core::make('helper/html')->css($packagePath.'/css/vivid-store.css','vivid-store'));   
    }  
    

}    