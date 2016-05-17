<?php
namespace Concrete\Package\VividStore\Src\VividStore\Payment\Methods\AuthNet;

use Core;
use Config;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as StorePaymentMethod;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Calculator as StoreCalculator;
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as StoreCustomer;
use \Omnipay\Omnipay;

class AuthNetPaymentMethod extends StorePaymentMethod
{
    public function dashboardForm()
    {
        $this->set('authnetLoginID', Config::get('vividstore.authnetLoginID'));
        $this->set('authnetTransactionKey', Config::get('vividstore.authnetTransactionKey'));
        //$this->set('authnetCurrency',Config::get('vividstore.authnetCurrency'));
        $this->set('authnetTestmode', Config::get('vividstore.authnetTestmode'));
        $this->set('form', Core::make("helper/form"));
        $form = Core::make("helper/form");
        $authnetLoginID = Config::get('vividstore.authnetLoginID');
    }
    
    public function save($data)
    {
        Config::save('vividstore.authnetLoginID', $data['authnetLoginID']);
        Config::save('vividstore.authnetTransactionKey', $data['authnetTransactionKey']);
        //Config::save('vividstore.authnetCurrency',$data['authnetCurrency']);
        Config::save('vividstore.authnetTestmode', $data['authnetTestmode']);
    }
    
    public function validate($args, $e)
    {
        $pm = StorePaymentMethod::getByHandle('auth_net');
        if ($args['paymentMethodEnabled'][$pm->getPaymentMethodID()]==1) {
            if ($args['authnetTransactionKey']=="") {
                $e->add(t("Transaction Key must be set"));
            }
            if ($args['authnetLoginID']=="") {
                $e->add(t("Login ID must be set"));
            }
        }
               
        return $e;
    }
    
    public function checkoutForm()
    {
        $this->set('form', Core::make("helper/form"));
        $years = array();
        $year = date("Y");
        for ($i=0;$i<15;$i++) {
            $years[$year+$i] = $year+$i;
        }
        $this->set("years", $years);
    }
    
    public function submitPayment()
    {

        $gateway = Omnipay::create('AuthorizeNet_AIM');
        $gateway->setApiLoginId(Config::get('vividstore.authnetLoginID'));
        $gateway->setTransactionKey(Config::get('vividstore.authnetTransactionKey'));
        $gateway->setDeveloperMode(Config::get('vividstore.authnetTestmode'));
        $customer = new StoreCustomer();
        $formData = array(
            'firstName' => $customer->getValue("billing_first_name"),
            'lastName' => $customer->getValue("billing_last_name"),
            'billingPhone' => $customer->getValue("billing_phone"),
            'email' => $customer->getEmail(),
            'number' => $_POST['authnet-checkout-credit-card'],
            'expiryMonth' => $_POST['authnet-checkout-exp-month'],
            'expiryYear' => $_POST['authnet-checkout-exp-year'],
            'cvv' => $_POST['authnet-checkout-ccv']
        );
        $response = $gateway->purchase(array(
            'amount' => StoreCalculator::getGrandTotal(),
            'currency' => 'USD',
            'card' => $formData
        ))->send();
        if ($response->isSuccessful()) {
            return array('error'=>0, 'transactionReference'=>$response->getTransactionReference());
        } else {
            // payment failed: display message to customer
            return array('error'=>1, 'errorMessage'=>$response->getMessage());
        }
    }
}
