<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use Controller;
use View;
use Illuminate\Filesystem\Filesystem;
use \Concrete\Package\VividStore\Src\VividStore\Order\Order as StoreOrder;

class OrderSlip extends Controller
{
    public function renderOrderPrintSlip()
    {
        $o = StoreOrder::getByID($this->post('oID'));
        if (Filesystem::exists(DIR_BASE."/application/elements/order_slip.php")) {
            View::element("order_slip", array('order'=>$o));
        } else {
            View::element("order_slip", array('order'=>$o), "vivid_store");
        }
    }
}
