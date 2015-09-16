<?php
namespace Concrete\Package\VividStore\Controller\SinglePage\Checkout;

use PageController;
use View;


use \Concrete\Package\VividStore\Src\VividStore\Orders\Order as VividOrder;
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
        $this->requireAsset('javascript', 'vivid-store');
        $this->requireAsset('css', 'vivid-store');
    }
    

}
