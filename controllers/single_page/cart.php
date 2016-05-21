<?php
namespace Concrete\Package\VividStore\Controller\SinglePage;

use PageController;
use View;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Calculator as StoreCalculator;

class cart extends PageController
{
    public function view()
    {
        $codeerror = false;
        $codesuccess = false;

        if ($this->isPost()) {
            if ($this->post('action') == 'update') {
                $data = $this->post();
                $result = StoreCart::update($data);
                $added = $result['added'];
                $returndata = array('success'=>true, 'quantity'=>(int)$data['pQty'], 'action'=>'update','added'=>$added);
            }

            if ($this->post('action') == 'clear') {
                StoreCart::clear();
                $returndata = array('success'=>true, 'action'=>'clear');
            }

            if ($this->post('action') == 'remove') {
                $data = $this->post();
                $result = StoreCart::remove($data['instance']);
                $returndata = array('success'=>true, 'action'=>'remove');
            }
        }

        $this->set('actiondata', $returndata);

        $this->set('cart', StoreCart::getCart());
        $this->set('total', StoreCalculator::getSubTotal());
        
        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\VividStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'vivid-store');
        $this->requireAsset('css', 'vivid-store');
    }
    public function add()
    {
        $data = $this->post();
        $result = StoreCart::add($data);

        $added = $result['added'];

        $product = StoreProduct::getByID($data['pID']);
        $productdata['pAutoCheckout'] = $product->autoCheckout();
        $productdata['pName'] = $product->getProductName();

        $returndata = array('success'=>true,'quantity'=>(int)$data['quantity'],'added'=>$added,'product'=>$productdata, 'action'=>'add');
        echo json_encode($returndata);
        exit();
    }

    public function code()
    {
        StoreCart::storeCode($this->post('code'));
        exit();
    }

    public function update()
    {
        $data = $this->post();
        $result = StoreCart::update($data);
        $added = $result['added'];
        $returndata = array('success'=>true, 'quantity'=>(int)$data['pQty'], 'action'=>'update','added'=>$added);
        echo json_encode($returndata);
        exit();
    }
    public function remove()
    {
        $instanceID = $_POST['instance'];
        StoreCart::remove($instanceID);
        $returndata = array('success'=>true,'action'=>'remove');
        echo json_encode($returndata);
        exit();
    }
    public function clear()
    {
        StoreCart::clear();
        $returndata = array('success'=>true,'action'=>'clear');
        echo json_encode($returndata);
        exit();
    }
}
