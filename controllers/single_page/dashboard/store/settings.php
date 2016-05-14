<?php
namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store;

use \Concrete\Core\Page\Controller\DashboardPageController;
use Package;
use Core;
use Loader;
use Config;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use \Concrete\Package\VividStore\Src\VividStore\Tax\TaxClass as StoreTaxClass;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as StorePaymentMethod;

class settings extends DashboardPageController
{
    //    public $error = Loader::helper("validation/error");
    public function on_start()
    {
    }

    public function view()
    {
        $this->loadFormAssets();
        $this->set("pageSelector", Core::make('helper/form/page_selector'));
        $this->set("countries", Core::make('helper/lists/countries')->getCountries());
        $this->set("states", Core::make('helper/lists/states_provinces')->getStates());
        $this->set("installedPaymentMethods", StorePaymentMethod::getMethods());
        $this->set("orderStatuses", StoreOrderStatus::getAll());
        $productPublishTarget = Config::get('vividstore.productPublishTarget');
        $this->set('productPublishTarget', $productPublishTarget);
    }
    public function loadFormAssets()
    {
        $pkg = Package::getByHandle('vivid_store');
        $pkgconfig = $pkg->getConfig();
        $this->set('pkgconfig', $pkgconfig);
        $this->addHeaderItem('<style type="text/css">.redactor_editor{padding:20px}</style>');
        $js = \Concrete\Package\VividStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('css', 'vividStoreDashboard');
        $this->requireAsset('javascript', 'vividStoreFunctions');
    }
    public function success()
    {
        $this->set('success', t('Settings Saved'));
        $this->view();
    }
    public function failed()
    {
        $this->view();
    }
    public function save()
    {
        $this->view();
        $args = $this->post();
        
        if ($this->isPost()) {
            $errors = $this->validate($args);
            $this->error = $errors;
            
            if (!$errors->has()) {
                Config::save('vividstore.symbol', $args['symbol']);
                Config::save('vividstore.whole', $args['whole']);
                Config::save('vividstore.thousand', $args['thousand']);
                Config::save('vividstore.taxenabled', $args['taxEnabled']);
                Config::save('vividstore.taxcountry', $args['taxCountry']);
                Config::save('vividstore.taxstate', $args['taxState']);
                Config::save('vividstore.taxcity', trim($args['taxCity']));
                Config::save('vividstore.taxAddress', trim($args['taxAddress']));
                Config::save('vividstore.taxMatch', trim($args['taxMatch']));
                Config::save('vividstore.taxBased', trim($args['taxBased']));
                Config::save('vividstore.taxrate', trim($args['taxRate']));
                Config::save('vividstore.taxName', trim($args['taxName']));
                Config::save('vividstore.calculation', trim($args['calculation']));
                Config::save('vividstore.shippingenabled', $args['shippingEnabled']);
                Config::save('vividstore.shippingbase', $args['shippingBasePrice']);
                Config::save('vividstore.shippingitem', $args['shippingItemPrice']);
                Config::save('vividstore.weightUnit', $args['weightUnit']);
                Config::save('vividstore.sizeUnit', $args['sizeUnit']);
                Config::save('vividstore.notificationemails', $args['notificationEmails']);
                Config::save('vividstore.emailalerts', $args['emailAlert']);
                Config::save('vividstore.emailalertsname', $args['emailAlertName']);
                Config::save('vividstore.productPublishTarget', $args['productPublishTarget']);
                Config::save('vividstore.guestCheckout', $args['guestCheckout']);

                //save payment methods
                if ($args['paymentMethodHandle']) {
                    foreach ($args['paymentMethodEnabled'] as $pmID=>$value) {
                        $pm = StorePaymentMethod::getByID($pmID);
                        $pm->setEnabled($value);
                        $controller = $pm->getMethodController();
                        $controller->save($args);
                    }
                    foreach ($args['paymentMethodDisplayName'] as $pmID=>$value) {
                        $pm = StorePaymentMethod::getByID($pmID);
                        $pm->setDisplayName($value);
                    }
                }

                $this->saveOrderStatuses($args);
                
                $this->redirect('/dashboard/store/settings/success');
            }//if no errors 
        }//if post
    }

    private function saveOrderStatuses($data)
    {
        if (isset($data['osID'])) {
            if ($data['osIsStartingStatus']) {
                $existingStartingStatus = StoreOrderStatus::getStartingStatus();
                if (is_object($existingStartingStatus)) {
                    $existingStartingStatus->setIsStartingStatus(false);
                    $existingStartingStatus->save();
                }
            }
            foreach ($data['osID'] as $key => $id) {
                $orderStatus = StoreOrderStatus::getByID($id);
                if (isset($data['osName'][$key]) && $data['osName'][$key]!='') {
                    $orderStatus->setName($data['osName'][$key]);
                } else {
                    $orderStatus->setName($orderStatus->getReadableHandle());
                }
                $orderStatus->setInformSite(isset($data['osInformSite'][$key]) ? 1 : 0);
                $orderStatus->setInformCustomer(isset($data['osInformCustomer'][$key]) ? 1 : 0);
                $orderStatus->setSortOrder($key);
                if ($data['osIsStartingStatus'] == $id) {
                    $orderStatus->setIsStartingStatus(true);
                }
                $orderStatus->save();
            }
        }
    }
    public function validate($args)
    {
        $e = Loader::helper('validation/error');
        
        if ($args['symbol']=="") {
            $e->add(t('You must set a currency symbol'));
        }
        if ($args['taxEnabled']=='yes') {
            if (!is_numeric(trim($args['taxRate']))) {
                $e->add(t('Tax Rate must be set, and a number'));
            }
        }
        if ($args['shippingEnabled']=='yes') {
            if (!is_numeric(trim($args['shippingBasePrice']))) {
                $e->add(t('Shipping Base Rate must be set, and a number'));
            }
            if (!is_numeric(trim($args['shippingItemPrice']))) {
                $e->add(t('Shipping Base Rate must be set, and a number (even if just zero)'));
            }
        }
        $paymentMethodsEnabled = 0;
        foreach ($args['paymentMethodEnabled'] as $method) {
            if ($method==1) {
                $paymentMethodsEnabled++;
            }
        }
        if ($paymentMethodsEnabled==0) {
            $e->add(t('At least one payment method must be enabled'));
        }
        foreach ($args['paymentMethodEnabled'] as $pmID=>$value) {
            $pm = StorePaymentMethod::getByID($pmID);
            $controller = $pm->getMethodController();
            $e = $controller->validate($args, $e);
        }

        if (!isset($args['osName'])) {
            $e->add(t('You must have at least one Order Status.'));
        }
        
        //before changing tax settings to "Extract", make sure there's only one rate per class
        $taxClasses = StoreTaxClass::getTaxClasses();
        foreach ($taxClasses as $taxClass) {
            $taxClassRates = $taxClass->getTaxClassRates();
            if (count($taxClassRates)>1) {
                $e->add(t("The %s Tax Class can't contain more than 1 Tax Rate if you change how the taxes are calculated", $taxClass->getTaxClassName()));
            }
        }
        
        return $e;
    }
}
