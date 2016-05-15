<?php
namespace Concrete\Package\VividStore\Controller\SinglePage;

use PageController;
use Core;
use View;
use Session;
use Config;
use Page;
use \Concrete\Package\VividStore\Src\VividStore\Order\Order as StoreOrder;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as StorePaymentMethod;
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as StoreCustomer;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Calculator as StoreCalculator;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Checkout as StoreCheckoutUtility;

class checkout extends PageController
{
    public function __construct()
    {
        parent::__construct(Page::getByPath('/checkout/'));
        $this->requiresLogin = StoreCart::requiresLogin();
        $this->customer = new StoreCustomer();
        $guestCheckout = Config::get('vividstore.guestCheckout');
        $this->guestCheckout = $guestCheckout ? $guestCheckout : 'off';
        $this->showLoginScreen = $this->showLoginScreen();
    }
    public function view()
    {
        if (StoreCart::getTotalItemsInCart() == 0) {
            $this->redirect("/cart/");
        }

        $this->set('customer', $this->customer);
        $this->set('form', Core::make("helper/form"));

        $this->set("states", Core::make('helper/lists/states_provinces')->getStates());
        $billingCountryArray = StoreCheckoutUtility::getCountryOptions();
        $shippingCountryArray = StoreCheckoutUtility::getCountryOptions('shipping');
        $this->set("billingCountries", $billingCountryArray['countries']);
        $this->set("shippingCountries", $shippingCountryArray['countries']);
        $this->set("defaultBillingCountry", $billingCountryArray['defaultCountry']);
        $this->set("defaultShippingCountry", $shippingCountryArray['defaultCountry']);

        $totals = StoreCalculator::getTotals();

        $this->set('subtotal', $totals['subTotal']);
        $this->set('taxes', $totals['taxes']);
        $this->set('taxtotal', $totals['taxTotal']);
        $this->set('shippingtotal', $totals['shippingTotal']);
        $this->set('total', $totals['total']);
        $this->set('shippingEnabled', StoreCart::isShippable());

        $this->getFooterAssets();

        $enabledMethods = StorePaymentMethod::getEnabledMethods();
        $availableMethods = array();
        foreach ($enabledMethods as $em) {
            $emmc = $em->getMethodController();
            if ($totals['total'] >= $emmc->getPaymentMinimum() && $totals['total'] <=  $emmc->getPaymentMaximum()) {
                $availableMethods[] = $em;
            }
        }

        $this->set("enabledPaymentMethods", $availableMethods);
    }

    public function getFooterAssets()
    {
        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\VividStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'vivid-store');
        $this->requireAsset('css', 'vivid-store');
    }

    public function showLoginScreen()
    {

        //this is a really dirty check we should move to another class.
        if ($this->customer->isGuest() && ($this->requiresLogin || $this->guestCheckout == 'off' || ($this->guestCheckout == 'option' && $_GET['guest'] != '1'))) {
            return true;
        } else {
            return false;
        }
    }

    public function hasGuestCheckout()
    {
        if ($this->guestCheckout == 'option' && !$this->requiresLogin) {
            return true;
        }
        return false;
    }
    
    public function getCartListElement()
    {
        $fileSystem = new \Illuminate\Filesystem\Filesystem;
        if ($fileSystem->exists(DIR_BASE.'/application/elements/cart_list.php')) {
            View::element('cart_list', array('cart'=>StoreCart::getCart()));
        } else {
            View::element('cart_list', array('cart'=>StoreCart::getCart()), 'vivid_store');
        }
    }
    
    public function failed()
    {
        $this->set('paymentErrors', Session::get('paymentErrors'));
        $this->view();
    }
    public function submit()
    {
        $data = $this->post();
        
        //process payment
        $pmHandle = $data['payment-method'];
        $pm = StorePaymentMethod::getByHandle($pmHandle);
        if ($pm === false) {
            //There was no payment method enabled somehow.
            //so we'll force invoice.
            $pm = StorePaymentMethod::getByHandle('invoice');
        }

        if ($pm->getMethodController()->external == true) {
            $pmsess = Session::get('paymentMethod');
            $pmsess[$pm->getPaymentMethodID()] = $data['payment-method'];
            Session::set('paymentMethod', $pmsess);
            $order = StoreOrder::add($data, $pm, null, 'incomplete');
            Session::set('orderID', $order->getOrderID());
            $this->redirect('/checkout/external');
        } else {
            $payment = $pm->submitPayment();
            if ($payment['error']==1) {
                $pmsess = Session::get('paymentMethod');
                $pmsess[$pm->getPaymentMethodID()] = $data['payment-method'];
                Session::set('paymentMethod', $pmsess);
                $errors = $payment['errorMessage'];
                Session::set('paymentErrors', $errors);
                $this->redirect("/checkout/failed#payment");
            } else {
                $transactionReference = $payment['transactionReference'];
                StoreOrder::add($data, $pm, $transactionReference);
                $this->redirect('/checkout/complete');
            }
        }
    }
    public function external()
    {
        $this->getFooterAssets();
        $pm = Session::get('paymentMethod');
        /*print_r($pm);
        exit();die();
        */
        foreach ($pm as $pmID=>$handle) {
            $pm = StorePaymentMethod::getByID($pmID);
        }
        //$pm = PaymentMethod::getByHandle($pm[3]);
        $this->set('pm', $pm);
        $this->set('action', $pm->getMethodController()->getAction());
    }
    public function validate()
    {
    }
}
