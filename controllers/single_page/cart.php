<?php
namespace Concrete\Package\VividStore\Controller\SinglePage;

use Page;
use PageController;
use Package;
use Core;
use View;
use Session;

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as VividCart;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
use \Concrete\Package\VividStore\Src\VividStore\Discount\DiscountRule as DiscountRule;

defined('C5_EXECUTE') or die(_("Access Denied."));
class Cart extends PageController
{
    public function view()
    {
        $codeerror = false;
        $codesuccess = false;

        if ($this->isPost()) {
            if ($this->post('action') == 'code' && $this->post('code')) {
                $codesuccess = VividCart::storeCode($this->post('code'));
                $codeerror = !$codesuccess;
            }
        }

        $this->set('codeerror', $codeerror);
        $this->set('codesuccess', $codesuccess);

        $this->set('cart',VividCart::getCart());
        $this->set('discounts',VividCart::getDiscounts());
        $this->set('total',VividCart::getSubTotal());
        $this->addHeaderItem("
            <script type=\"text/javascript\">
                var PRODUCTMODAL = '".View::url('/productmodal')."';
                var CARTURL = '".View::url('/cart')."';
                var CHECKOUTURL = '".View::url('/checkout')."';
            </script>
        ");
        $this->addFooterItem(Core::make('helper/html')->javascript('vivid-store.js','vivid_store'));   
        $this->addHeaderItem(Core::make('helper/html')->css('vivid-store.css','vivid_store'));

        $discountsWithCodesExist = DiscountRule::discountsWithCodesExist();
        $this->set("discountsWithCodesExist",$discountsWithCodesExist);
    }  
    public function add()
    {
        $data = $this->post();
        VividCart::add($data);
        $product = VividProduct::getByID($data['pID']);
        $returndata = array('success'=>true,'quantity'=>(int)$data['quantity'],'product'=>$product);
        echo json_encode($returndata);
        exit();

    }

    public function code() {
        VividCart::storeCode($this->post('code'));
        exit();
    }

    public function update()
    {
        $data = $this->post();
        VividCart::update($data);
        $returndata = array('success'=>true);
        echo json_encode($returndata);
        exit();
    }
    public function remove()
    {
        $instanceID = $_POST['instance'];
        VividCart::remove($instanceID);
        $returndata = array('success'=>true);
        echo json_encode($returndata);
        exit();
    }
    public function clear()
    {
        VividCart::clear();
        $this->view();
    }
}    
