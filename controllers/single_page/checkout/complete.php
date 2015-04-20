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
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as Customer;

defined('C5_EXECUTE') or die(_("Access Denied."));
class Complete extends PageController
{
    public function view()
    {
        $customer = new Customer();
        $order = VividOrder::getByID($customer->getLastOrderID());

        if(is_object($order)){
            $this->set("order",$order);
        } else {
            $this->redirect("/cart");
        }
        $pkg = Package::getByHandle('vivid_store');
        $packagePath = $pkg->getRelativePath();
        $this->addFooterItem(Core::make('helper/html')->javascript($packagePath.'/js/vivid-store.js','vivid-store'));
        $this->addHeaderItem(Core::make('helper/html')->css($packagePath.'/css/vivid-store.css','vivid-store'));   
    }  
    

}    