<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use \Concrete\Core\Controller\Controller;
use View;
use Illuminate\Filesystem\Filesystem;

use \Concrete\Package\VividStore\Src\VividStore\Orders\Order;

defined('C5_EXECUTE') or die(_("Access Denied."));

class OrderSlip extends Controller
{
    public function renderOrderPrintSlip()
    {
        $o = Order::getByID($this->post('oID'));
        if(Filesystem::exists(DIR_BASE."/application/elements/order_slip.php")){
            View::element("order_slip",array('order'=>$o));
        } else {
            View::element("order_slip",array('order'=>$o),"vivid_store");
        }
    }    
}
