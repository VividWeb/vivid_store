<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store;

use \Concrete\Core\Page\Controller\DashboardPageController;
use Database;
use Package;
use Core;
use Loader;
use Concrete\Package\VividStore\Src\VividStore\Orders\OrderStatus\OrderStatus;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as PaymentMethod;

defined('C5_EXECUTE') or die("Access Denied.");
class Settings extends DashboardPageController
{

//    public $error = Loader::helper("validation/error");

    public function on_start()
    {
        
    }
    public function view(){
       $this->loadFormAssets();
       $this->set("pageSelector",Core::make('helper/form/page_selector'));
       $this->set("countries",Core::make('helper/lists/countries')->getCountries());
       $this->set("states",Core::make('helper/lists/states_provinces')->getStates());
       $this->set("installedPaymentMethods",PaymentMethod::getMethods());
       $this->set("orderStatuses",OrderStatus::getAll());
       $pkg = Package::getByHandle('vivid_store');
       $productPublishTarget = $pkg->getConfig()->get('vividstore.productPublishTarget');
       $this->set('productPublishTarget',$productPublishTarget);
    }
    public function loadFormAssets()
    {
        $pkg = Package::getByHandle('vivid_store');
        $pkgconfig = $pkg->getConfig();
        $this->set('pkgconfig',$pkgconfig);
        $packagePath = $pkg->getRelativePath();
        $this->addHeaderItem('<style type="text/css">.redactor_editor{padding:20px}</style>');
        $this->addHeaderItem(Core::make('helper/html')->css($packagePath.'/css/vividStoreDashboard.css'));
        $this->addFooterItem(Core::make('helper/html')->javascript($packagePath.'/js/vividStoreFunctions.js'));
    }
    public function success()
    {
        $this->set('success',t('Settings Saved!'));
        $this->view();
    }
    public function failed()
    {
        $this->view();
    }
    public function save()
    {
        $this->view();
        $pkg = Package::getByHandle('vivid_store');
        $args = $this->post();   
        
        if ($this->isPost()) {
            $errors = $this->validate($args);
            $this->error = $errors;
            
            if (!$errors->has()) {
                
                $pkg->getConfig()->save('vividstore.symbol',$args['symbol']);
                $pkg->getConfig()->save('vividstore.whole',$args['whole']);
                $pkg->getConfig()->save('vividstore.thousand',$args['thousand']);
                $pkg->getConfig()->save('vividstore.taxenabled',$args['taxEnabled']);
                $pkg->getConfig()->save('vividstore.taxcountry',$args['taxCountry']);
                $pkg->getConfig()->save('vividstore.taxstate',$args['taxState']);
                $pkg->getconfig()->save('vividstore.taxcity',trim($args['taxCity']));
                $pkg->getconfig()->save('vividstore.taxAddress',trim($args['taxAddress']));
                $pkg->getconfig()->save('vividstore.taxMatch',trim($args['taxMatch']));
                $pkg->getconfig()->save('vividstore.taxBased',trim($args['taxBased']));
                $pkg->getconfig()->save('vividstore.taxrate',trim($args['taxRate']));
                $pkg->getConfig()->save('vividstore.shippingenabled',$args['shippingEnabled']);
                $pkg->getConfig()->save('vividstore.shippingbase',$args['shippingBasePrice']);
                $pkg->getConfig()->save('vividstore.shippingitem',$args['shippingItemPrice']);
                $pkg->getConfig()->save('vividstore.weightUnit',$args['weightUnit']);
                $pkg->getConfig()->save('vividstore.sizeUnit',$args['sizeUnit']);
                $pkg->getConfig()->save('vividstore.notificationemails',$args['notificationEmails']);
                $pkg->getConfig()->save('vividstore.emailalerts',$args['emailAlert']);
                $pkg->getConfig()->save('vividstore.productPublishTarget',$args['productPublishTarget']);
                
                //save payment methods
                if($args['paymentMethodHandle']){
                    
                    foreach($args['paymentMethodEnabled'] as $pmID=>$value){
                        $pm = PaymentMethod::getByID($pmID);
                        $pm->setEnabled($value);
                        $controller = $pm->getMethodController();
                        $controller->save($args);
                    }
                    foreach($args['paymentMethodDisplayName'] as $pmID=>$value){
                        $pm = PaymentMethod::getByID($pmID);
                        $pm->setDisplayName($value);
                    }
                }       

                $this->saveOrderStatuses($args);
                
                $this->redirect('/dashboard/store/settings/success');
                
            }//if no errors 
            
        }//if post
         
    }

    private function saveOrderStatuses($data) {
        if (isset($data['osID'])) {
            foreach ($data['osID'] as $key => $id) {
                $orderStatus = OrderStatus::getByID($id);
                $orderStatusSettings = array(
                    'osName' => ((isset($data['osName'][$key]) && $data['osName'][$key]!='') ?
                        $data['osName'][$key] : $orderStatus->getReadableHandle()),
                    'osInformSite' => isset($data['osInformSite'][$key]) ? 1 : 0,
                    'osInformCustomer' => isset($data['osInformCustomer'][$key]) ? 1 : 0,
                    'osSortOrder' => $key
                );
                $orderStatus->update($orderStatusSettings);
            }
            if (isset($data['osIsStartingStatus'])) {
                OrderStatus::setNewStartingStatus(OrderStatus::getByID($data['osIsStartingStatus'])->getHandle());
            } else {
                $orderStatuses = OrderStatus::getAll();
                OrderStatus::setNewStartingStatus($orderStatuses[0]);
            }
        }
    }
    public function validate($args)
    {
        $e = Loader::helper('validation/error');
        
        if($args['symbol']==""){
            $e->add(t('You must set a currency symbol'));
        }
        if($args['taxEnabled']=='yes'){
            if(!is_numeric(trim($args['taxRate']))){
                $e->add(t('Tax Rate must be set, and a number'));
            }
            if($args['taxState']==""){
                $e->add(t('Tax State must be set'));
            }
            if($args['taxCity']==""){
                $e->add(t('Tax City must be set'));
            }
        }
        if($args['shippingEnabled']=='yes'){
            if(!is_numeric(trim($args['shippingBasePrice']))){
                $e->add(t('Shipping Base Rate must be set, and a number'));
            }
            if(!is_numeric(trim($args['shippingItemPrice']))){
                $e->add(t('Shipping Base Rate must be set, and a number (even if just zero)'));
            }
        }
        $paymentMethodsEnabled = 0;
        foreach($args['paymentMethodEnabled'] as $method){
            if($method==1){
                $paymentMethodsEnabled++;
            }
        }
        if($paymentMethodsEnabled==0){
            $e->add(t('At least one payment method must be enabled'));
        }
        foreach($args['paymentMethodEnabled'] as $pmID=>$value){
            $pm = PaymentMethod::getByID($pmID);
            $controller = $pm->getMethodController();
            $e = $controller->validate($args,$e);
        }

        if (!isset($args['osName'])) {
            $e->add(t('You must have at least one Order Status.'));
        }
        return $e;
        
    }

}
