<?php
namespace Concrete\Package\VividStore\src\VividStore\Payment;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as PaymentMethod;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as VividCart;
use Concrete\Package\VividStore\Src\VividStore\Utilities\Price;
use Package;
use Core;
use AuthorizeNetAIM;

defined('C5_EXECUTE') or die(_("Access Denied."));
class AuthNetPaymentMethod extends PaymentMethod
{
    public function dashboardForm()
    {
        $pkg = Package::getByHandle("vivid_store");
        $pkgconfig = $pkg->getConfig();
        $this->set('authnetLoginID',$pkgconfig->get('vividstore.authnetLoginID'));
        $this->set('authnetTransactionKey',$pkgconfig->get('vividstore.authnetTransactionKey'));
        $this->set('authnetCurrency',$pkgconfig->get('vividstore.authnetCurrency'));
        $this->set('authnetTestmode',$pkgconfig->get('vividstore.authnetTestmode'));
        $this->set('form',Core::make("helper/form"));
        $form = Core::make("helper/form");
        $authnetLoginID = $pkgconfig->get('vividstore.authnetLoginID');
    }
    
    public function save($data)
    {
         
        $pkg = Package::getByHandle("vivid_store");
        $pkg->getConfig()->save('vividstore.authnetLoginID',$data['authnetLoginID']);
        $pkg->getConfig()->save('vividstore.authnetTransactionKey',$data['authnetTransactionKey']);
        $pkg->getConfig()->save('vividstore.authnetCurrency',$data['authnetCurrency']);
        $pkg->getConfig()->save('vividstore.authnetTestmode',$data['authnetTestmode']);
        
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
        $pkg = Package::getByHandle('vivid_store');
        $pkgconfig = $pkg->getConfig();
        $METHOD_TO_USE = "AIM";
        define("AUTHORIZENET_API_LOGIN_ID",$pkgconfig->get('vividstore.authnetLoginID'));    // Add your API LOGIN ID
        define("AUTHORIZENET_TRANSACTION_KEY",$pkgconfig->get('vividstore.authnetTransactionKey')); // Add your API transaction key
        define("AUTHORIZENET_SANDBOX",$pkgconfig->get('vividstore.authnetTestmode'));       // Set to false to test against production
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