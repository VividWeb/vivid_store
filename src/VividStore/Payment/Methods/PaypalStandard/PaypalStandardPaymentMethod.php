<?php
namespace Concrete\Package\VividStore\Src\VividStore\Payment\Methods\PaypalStandard;

use Core;
use URL;
use Config;
use Session;
use Log;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as StorePaymentMethod;
use \Concrete\Package\VividStore\Src\VividStore\Order\Order as StoreOrder;
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as StoreCustomer;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Calculator as StoreCalculator;

class PaypalStandardPaymentMethod extends StorePaymentMethod
{
    public $external = true;
    
    public function dashboardForm()
    {
        $this->set('paypalEmail', Config::get('vividstore.paypalEmail'));
        $this->set('paypalTestMode', Config::get('vividstore.paypalTestMode'));
        $this->set('paypalCurrency', Config::get('vividstore.paypalCurrency'));
        $currencies = array(
            'AUD' => "Australian Dollar",
            'CAD' => "Canadian Dollar",
            'CZK' => "Czech Koruna",
            'DKK' => "Danish Krone",
            'EUR' => "Euro",
            'HKD' => "Hong Kong Dollar",
            'HUF' => "Hungarian Forint",
            'ILS' => "Israeli New Sheqel",
            'JPY' => "Japanese Yen",
            'MXN' => "Mexican Peso",
            'NOK' => "Norwegian Krone",
            'NZD' => "New Zealand Dollar",
            'PHP' => "Philippine Peso",
            'PLN' => "Polish Zloty",
            'GBP' => "Pound Sterling",
            'SGD' => "Singapore Dollar",
            'SEK' => "Swedish Krona",
            'CHF' => "Swiss Franc",
            'TWD' => "Taiwan New Dollar",
            'THB' => "Thai Baht",
            'USD' => "U.S. Dollar"
        );
        $this->set('currencies', $currencies);
        $this->set('form', Core::make("helper/form"));
    }
    
    public function save($data)
    {
        Config::save('vividstore.paypalEmail', $data['paypalEmail']);
        Config::save('vividstore.paypalTestMode', $data['paypalTestMode']);
        Config::save('vividstore.paypalCurrency', $data['paypalCurrency']);
    }
    public function validate($args, $e)
    {
        $pm = StorePaymentMethod::getByHandle('paypal_standard');
        if ($args['paymentMethodEnabled'][$pm->getPaymentMethodID()]==1) {
            if ($args['paypalEmail']=="") {
                $e->add(t("PayPal Email must be set"));
            }
        }
        return $e;
    }
    public function checkoutForm()
    {
        //nada
    }
    public function redirectForm()
    {
        $customer = new StoreCustomer();
        $totals = StoreCalculator::getTotals();
        $paypalEmail = Config::get('vividstore.paypalEmail');
        $order = StoreOrder::getByID(Session::get('orderID'));
        $this->set('paypalEmail', $paypalEmail);
        $this->set('siteName', Config::get('concrete.site'));
        $this->set('customer', $customer);
        $this->set('total', $order->getTotal());
        $this->set('notifyURL', URL::to('/checkout/paypalresponse'));
        $this->set('orderID', $order->getOrderID());
        $this->set('returnURL', URL::to('/checkout/complete'));
        $currencyCode = Config::get('vividstore.paypalCurrency');
        if (!$currencyCode) {
            $currencyCode = "USD";
        }
        $this->set('currencyCode', $currencyCode);
    }
    
    public function submitPayment()
    {
        
        //nothing to do except return true
        return array('error'=>0, 'transactionReference'=>'');
    }
    public function getAction()
    {
        if (Config::get('vividstore.paypalTestMode')==true) {
            return "https://www.sandbox.paypal.com/cgi-bin/webscr";
        } else {
            return "https://www.paypal.com/cgi-bin/webscr";
        }
    }
    public static function validateCompletion()
    {
        // Read POST data
        // reading posted data directly from $_POST causes serialization
        // issues with array data in POST. Reading raw POST data from input stream instead.
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }
        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }
        // Post IPN data back to PayPal to validate the IPN data is genuine
        // Without this step anyone can fake IPN data
        if (Config::get('vividstore.paypalTestMode') == true) {
            $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        } else {
            $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
        }
        $ch = curl_init($paypal_url);
        if ($ch == false) {
            return false;
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        if (DEBUG == true) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }
        
        // CONFIG: Optional proxy configuration
        //curl_setopt($ch, CURLOPT_PROXY, $proxy);
        //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        // Set TCP timeout to 30 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        
        // CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
        // of the certificate as shown below. Ensure the file is readable by the webserver.
        // This is mandatory for some environments.
        //$cert = __DIR__ . "./cacert.pem";
        //curl_setopt($ch, CURLOPT_CAINFO, $cert);

        $res = curl_exec($ch);
        if (curl_errno($ch) != 0) {
            // cURL error

            Log::addEntry("Can't connect to PayPal to validate IPN message: " . curl_error($ch));
            curl_close($ch);
            exit;
        } else {
            //if we want to log more stuff
            //Log::addEntry("HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req");
            //Log::addEntry("HTTP response of validation request: $res");
            curl_close($ch);
        }
        // Inspect IPN validation result and act accordingly
        // Split response headers and payload, a better way for strcmp
        $tokens = explode("\r\n\r\n", trim($res));
        $res = trim(end($tokens));
        if (strcmp($res, "VERIFIED") == 0) {
            $order = StoreOrder::getByID($_POST['invoice']);
            $order->completeOrder($_POST['txn_id']);
            $order->updateStatus(StoreOrderStatus::getStartingStatus()->getHandle());
        } elseif (strcmp($res, "INVALID") == 0) {
            // log for manual investigation
            // Add business logic here which deals with invalid IPN messages
            Log::addEntry("Invalid IPN: $req");
        }
    }


    public function getPaymentMinimum()
    {
        return 0.03;
    }
}
