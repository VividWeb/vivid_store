<?php
namespace Concrete\Package\VividStore\Src\VividStore\Payment\Methods\AuthNet;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as PaymentMethod;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as VividCart;
use Concrete\Package\VividStore\Src\VividStore\Utilities\Price;
use Package;
use Core;
use Config;
use AuthorizeNetAIM;

defined('C5_EXECUTE') or die(_("Access Denied."));
class AuthNetPaymentMethod extends PaymentMethod
{
    public function dashboardForm()
    {
        $this->set('authnetLoginID',Config::get('vividstore.authnetLoginID'));
        $this->set('authnetTransactionKey',Config::get('vividstore.authnetTransactionKey'));
        $this->set('authnetCurrency',Config::get('vividstore.authnetCurrency'));
        $this->set('authnetTestmode',Config::get('vividstore.authnetTestmode'));
        $this->set('form',Core::make("helper/form"));
        $form = Core::make("helper/form");
        $authnetLoginID = Config::get('vividstore.authnetLoginID');
    }
    
    public function save($data)
    {
        Config::save('vividstore.authnetLoginID',$data['authnetLoginID']);
        Config::save('vividstore.authnetTransactionKey',$data['authnetTransactionKey']);
        Config::save('vividstore.authnetCurrency',$data['authnetCurrency']);
        Config::save('vividstore.authnetTestmode',$data['authnetTestmode']);
    }
    
    public function validate($args,$e)
    {
        $pm = PaymentMethod::getByHandle('auth_net');    
        if($args['paymentMethodEnabled'][$pm->getPaymentMethodID()]==1){
            if($args['authnetTransactionKey']==""){
                $e->add(t("Transaction Key must be set"));     
            }   
            if($args['authnetLoginID']==""){
                $e->add(t("Login ID must be set"));     
            }
        }  
               
        return $e;
        
    }
    
    public function checkoutForm()
    {
        $this->set('form',Core::make("helper/form"));  
        $years = array();
        $year = date("Y");
        for($i=0;$i<15;$i++){
            $years[$year+$i] = $year+$i;
        }
        $this->set("years",$years);
    }
    
    public function submitPayment()
    {
        $dir = $this->getMethodDirectory();
        require_once $dir.'anet_php_sdk/AuthorizeNet.php';
        $METHOD_TO_USE = "AIM";
        define("AUTHORIZENET_API_LOGIN_ID",Config::get('vividstore.authnetLoginID'));    // Add your API LOGIN ID
        define("AUTHORIZENET_TRANSACTION_KEY",Config::get('vividstore.authnetTransactionKey')); // Add your API transaction key
        define("AUTHORIZENET_SANDBOX",Config::get('vividstore.authnetTestmode'));       // Set to false to test against production
        define("TEST_REQUEST", "FALSE");           // You may want to set to true if testing against production
        //define("AUTHORIZENET_MD5_SETTING","");                // Add your MD5 Setting.
        //$site_root = ""; // Add the URL to your site
        
        if (AUTHORIZENET_API_LOGIN_ID == "") {
            die('Enter your merchant credentials');
        }
        $transaction = new AuthorizeNetAIM;
        $transaction->setSandbox(AUTHORIZENET_SANDBOX);
        $transaction->setFields(
            array(
                'amount' => Price::getFloat(VividCart::getTotal()), 
                'card_num' => $_POST['authnet-checkout-credit-card'], 
                'exp_date' => $_POST['authnet-checkout-exp-month'].$_POST['authnet-checkout-exp-year']
            )
        );
        $response = $transaction->authorizeAndCapture();
        if ($response->approved) {
            return true;
        } else {
            return array('error'=>1,'errorMessage'=>$response->error_message." Error Code: ".$response->response_code. ". Message: ".$response->response_reason_text);
        }

        
    }

    
}

return __NAMESPACE__;