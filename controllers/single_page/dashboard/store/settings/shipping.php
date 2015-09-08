<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store\Settings;

use \Concrete\Core\Page\Controller\DashboardPageController;
use View;
use Loader;

use \Concrete\Package\VividStore\Src\VividStore\Shipping\MethodType as ShippingMethodType;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\Method as ShippingMethod;
defined('C5_EXECUTE') or die("Access Denied.");

class Shipping extends DashboardPageController
{
    
    public function view()
    {
        $this->set("methodTypes",ShippingMethodType::getAvailableMethodTypes());
    }
    public function add($smtID)
    {
        $this->set('pageTitle',t("Add Shipping Method"));
        $smt = ShippingMethodType::getByID($smtID);
        $this->set('smt',$smt);
        $this->set("task",t("Add"));
    }
    public function edit($smID)
    {
        $this->set('pageTitle',t("Edit Shipping Method"));
        $sm = ShippingMethod::getByID($smID);
        $smt = $sm->getShippingMethodType();
        $this->set('sm',$sm);
        $this->set('smt',$smt);
        $this->set("task",t("Update"));
    }
    public function delete($smID)
    {
        $sm = ShippingMethod::getByID($smID);
        $sm->delete();
        $this->redirect('/dashboard/store/settings/shipping/removed');
    }
    public function success()
    {
        $this->view();
        $this->set("message",t("Successfully added a new Shipping Method"));
    }
    public function updated()
    {
        $this->view();
        $this->set("message",t("Successfully updated"));
    }
    public function removed()
    {
        $this->view();
        $this->set("message",t("Successfully removed"));
    }
    public function add_method()
    {
        $data = $this->post();
        $errors = $this->validate($data);
        $this->error = null; //clear errors
        $this->error = $errors;
        if (!$errors->has()) {
            if($this->post('shippingMethodID')){
                //update
                $shippingMethod = ShippingMethod::getByID($this->post('shippingMethodID'));
                $shippingMethodTypeMethod = $shippingMethod->getShippingMethodTypeMethod();
                $shippingMethodTypeMethod->update($this->post());
                $shippingMethod->update($this->post('methodName'),$this->post('methodEnabled'));
                $this->redirect('/dashboard/store/settings/shipping/updated');
            } else {
                //first we send the data to the shipping method type.
                $shippingMethodType = ShippingMethodType::getByID($this->post('shippingMethodTypeID'));
                $shippingMethodTypeMethod = $shippingMethodType->addMethod($this->post());
                //make a shipping method that correlates with it.
                ShippingMethod::add($shippingMethodTypeMethod,$shippingMethodType,$this->post('methodName'),$this->post('methodEnabled'));
                $this->redirect('/dashboard/store/settings/shipping/success');
            }
        } else {
            $this->add($this->post('shippingMethodTypeID'));
            //$smt = ShippingMethodType::getByID($this->post('shippingMethodTypeID'));
            //$this->set('smt',$smt);
        }
                
        
    }
    public function validate($data)
    {
        $this->error = null;
        $e = Loader::helper('validation/error');
        
        //check our manditory fields
        if($data['methodName']==""){
            $e->add(t("Method Name must be set"));
        }
        if(!is_numeric($data['minimumAmount'])){
            $e->add(t("Minimum Amount must be numeric"));
        }
        if(!is_numeric($data['maximumAmount'])){
            $e->add(t("Maximum Amount must be numeric"));
        }
        
        //pass the validator to the shipping method to check for it's own errors
        $shippingMethodType = ShippingMethodType::getByID($data['shippingMethodTypeID']);
        $e = $shippingMethodType->getMethodTypeController()->validate($data,$e);
        
        return $e;
        
    }
}
