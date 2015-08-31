<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use \Concrete\Core\Controller\Controller;

use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as Customer;

defined('C5_EXECUTE') or die(_("Access Denied."));

class OrderSlip extends Controller
{
    public function renderOrderPrintSlip()
    {
        $p = Product::getByID($this->post('pID'));
        if(Filesystem::exists(DIR_BASE."/application/elements/product_slip.php")){
            View::element("product_sip",$p);
        } else {
            View::element("product_slip",$p,"vivid_store");
        }
    }    
}
