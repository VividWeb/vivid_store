<?php
namespace Concrete\Package\VividStore\Controller\SinglePage;
use Page;
use PageController;
use Core;
use \Concrete\Core\Localization\Service\CountryList;
use View;
use Package;
use User;
use UserInfo;
use Session;
use Config;
use Loader;
use UserAttributeKey;

use \Concrete\Package\VividStore\Src\VividStore\Orders\Order as VividOrder;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as VividCart;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as PaymentMethod;
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as Customer;

defined('C5_EXECUTE') or die(_("Access Denied."));
class Checkout extends PageController
{
    public function view()
    {
        $pkg = Package::getByHandle('vivid_store');

        $customer = new Customer();
        $this->set('customer', $customer);
        $guestCheckout = Config::get('vividstore.guestCheckout');
        $this->set('guestCheckout', ($guestCheckout ? $guestCheckout : 'off'));
        $this->set('requiresLogin', VividCart::requiresLogin());

        if(VividCart::getTotalItemsInCart() == 0){
            $this->redirect("/cart/");
        }
        $this->set('form',Core::make("helper/form"));

        $allcountries = Core::make('helper/lists/countries')->getCountries();

        $db = Loader::db();

        $ak = UserAttributeKey::getByHandle('billing_address');
        $row = $db->GetRow(
            'select akHasCustomCountries, akDefaultCountry from atAddressSettings where akID = ?',
            array($ak->getAttributeKeyID())
        );

        $defaultBillingCountry = $row['akDefaultCountry'];

        if ($row['akHasCustomCountries'] == 1) {
            $availableBillingCountries = $db->GetCol(
                'select country from atAddressCustomCountries where akID = ?',
                array($ak->getAttributeKeyID())
            );

            $billingCountries = array();
            foreach($availableBillingCountries as $countrycode) {
                $billingCountries[$countrycode] = $allcountries[$countrycode];
            }
        } else {
            $billingCountries =  $allcountries;
        }

        $ak = UserAttributeKey::getByHandle('shipping_address');
        $row = $db->GetRow(
            'select akHasCustomCountries, akDefaultCountry from atAddressSettings where akID = ?',
            array($ak->getAttributeKeyID())
        );

        $defaultShippingCountry = $row['akDefaultCountry'];

        if ($row['akHasCustomCountries'] == 1) {
            $availableShippingCountries = $db->GetCol(
                'select country from atAddressCustomCountries where akID = ?',
                array($ak->getAttributeKeyID())
            );

            $shippingCountries = array();
            foreach($availableShippingCountries as $countrycode) {
                $shippingCountries[$countrycode] = $allcountries[$countrycode];
            }
        } else {
            $shippingCountries = $allcountries;
        }


        $this->set("billingCountries",$billingCountries);
        $this->set("shippingCountries",$shippingCountries);

        $this->set("defaultBillingCountry",$defaultBillingCountry);
        $this->set("defaultShippingCountry",$defaultShippingCountry);

        $this->set("states",Core::make('helper/lists/states_provinces')->getStates());

        $totals = VividCart::getTotals();

        $this->set('subtotal',$totals['subTotal']);
        $this->set('taxes',$totals['taxes']);

        $taxBased = Config::get('vividstore.taxBased');
        $taxlabel = Config::get('vividstore.taxName');
        
        $this->set('taxtotal',$totals['taxTotal']);

        $this->set('shippingtotal',$totals['shippingTotal']);
        $this->set('total',$totals['total']);

        $this->addHeaderItem("
            <script type=\"text/javascript\">
                var PRODUCTMODAL = '".View::url('/productmodal')."';
                var CARTURL = '".View::url('/cart')."';
                var CHECKOUTURL = '".View::url('/checkout')."';
            </script>
        ");

        $packagePath = $pkg->getRelativePath();
        $this->addFooterItem(Core::make('helper/html')->javascript($packagePath.'/js/vivid-store.js','vivid-store'));   
        $this->addHeaderItem(Core::make('helper/html')->css($packagePath.'/css/vivid-store.css','vivid-store'));   
        $this->addFooterItem("
            <script type=\"text/javascript\">
                vividStore.loadViaHash();
            </script>
        ");
        $this->set("enabledPaymentMethods",PaymentMethod::getEnabledMethods());
        
        
    }  
    
    public function failed()
    {
        $this->set('paymentErrors',Session::get('paymentErrors'));
        $this->view();   
    }
    public function submit()
    {
        $data = $this->post();
        
        //process payment
        $pmHandle = $data['payment-method'];
        $pm = PaymentMethod::getByHandle($pmHandle);
        if(!($pm instanceof PaymentMethod)){
            //There was no payment method enabled somehow.
            //so we'll force invoice.
            $pm = PaymentMethod::getByHandle('invoice');
        }
        $payment = $pm->submitPayment();
        if($payment['error']==1){
            $pmsess = Session::get('paymentMethod');  
            $pmsess[$pm->getPaymentMethodID()] = $data['payment-method'];
            Session::set('paymentMethod',$pmsess);
            $pesess = Session::get('paymentErrors');
            $pesess = $payment['errorMessage'];
            Session::set('paymentErrors',$pesess);
            $this->redirect("/checkout/failed#payment");
        } else {
            VividOrder::add($data,$pm);    
            $this->redirect('/checkout/complete');
        }  
    }
    public function validate()
    {
        
    }

}    