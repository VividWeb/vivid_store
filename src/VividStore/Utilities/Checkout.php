<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use \Concrete\Core\Controller\Controller as RouteController;
use Core;
use Loader;
use Session;
use Illuminate\Filesystem\Filesystem;
use View;

use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as Customer;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Checkout extends RouteController
{
    //public $error;
    public function updater()
    {
        if(isset($_POST)){
            $data = $_POST;
            $billing = false;
            if ($data['adrType']=='billing'){ $billing=true; }
            $e = $this->validateAddress($data,$shipping);
            if($e->has()){
                echo $e->outputJSON();
            } else {
                if ($data['adrType']=='billing'){
                    $this->updateBilling($data);
                }
                if ($data['adrType']=='shipping'){
                    $this->updateShipping($data);
                }
                echo json_encode(array("error"=>false));
            }
        } else {
            echo "Im not sure youre supposed to be here.";
        }
    }
    
    private function updateBilling($data)
    {
        //update the users billing address
        $customer = new Customer();

        if ($customer->isGuest()){
            $customer->setEmail(trim($data['email']));
        }

        $customer->setValue("billing_first_name",trim($data['fName']));
        Session::set('billing_first_name',trim($data['fName']));
        $customer->setValue("billing_last_name",trim($data['lName']));
        Session::set('billing_last_name',trim($data['lName']));
        $customer->setValue("billing_phone",trim($data['phone']));
        Session::set('billing_phone',trim($data['phone']));
        $address = array(
            "address1"=>trim($data['addr1']),
            "address2"=>trim($data['addr2']),
            "city"=>trim($data['city']),
            "state_province"=>trim($data['state']),
            "postal_code"=>trim($data['postal']),
            "country"=>trim($data['count']),
        );
        $customer->setValue("billing_address",$address);
        Session::set('billing_address',$address);
    }

    public function updateShipping($data)
    {
        //update the users shipping address
        $this->validateAddress($data);
        $customer = new Customer();
        $customer->setValue("shipping_first_name",trim($data['fName']));
        Session::set('shipping_first_name',trim($data['fName']));
        $customer->setValue("shipping_last_name",trim($data['lName']));
        Session::set('shipping_last_name',trim($data['lName']));
        $address = array(
            "address1"=>trim($data['addr1']),
            "address2"=>trim($data['addr2']),
            "city"=>trim($data['city']),
            "state_province"=>trim($data['state']),
            "postal_code"=>trim($data['postal']),
            "country"=>trim($data['count']),
        );
        $customer->setValue("shipping_address",$address);
        Session::set('shipping_address',$address);
    }
    
    public function validateAddress($data,$billing=null)
    {
        $e = Core::make('helper/validation/error');
        $vals = Loader::helper('validation/strings');
        $customer = new Customer();

        if($billing){
            if ($customer->isGuest()) {
                if (!$vals->email($data['email'])) {
                    $e->add(t('You must enter a valid email address'));
                }
            }
        }

        if(strlen($data['fName']) < 1){
            $e->add(t('You must enter a first name'));
        }
        if(strlen($data['fName']) > 30){
            $e->add(t('Your First Name is quite long. Please keep it under 30 characters'));
        }
        if(strlen($data['lName']) < 3){
            $e->add(t('You must enter a Last Name'));
        }
        if(strlen($data['lName']) > 30){
            $e->add(t('That is a long Last Name. Please keep it under 30 characters'));
        }
        if(strlen($data['addr1']) < 3 ){
            $e->add(t('You must enter an address'));
        }
        if(strlen($data['addr1']) > 50 ){
            $e->add(t('That is a long street name. Please keep it under 50 characters'));
        }
        if(strlen($data['count']) < 2){
            $e->add(t('You must enter a Country'));
        }
        if(strlen($data['count']) > 30){
            $e->add(t('You did not select a Country from the list.'));
        }
        if(strlen($data['city']) < 2){
            $e->add(t('You must enter a City'));
        }
        if(strlen($data['city']) > 30){
            $e->add(t('You must enter a valid City'));
        }
        if(strlen($data['postal']) > 10){
            $e->add(t('You must enter a valid Postal Code'));
        }
        if(strlen($data['postal']) < 2){
            $e->add(t('You must enter a valid Postal Code'));
        }
        
        return $e;

    }

    public function getShippingMethods()
    {
        if(Filesystem::exists(DIR_BASE."/application/elements/checkout/shipping_methods.php")){
            View::element("checkout/shipping_methods");
        } else {
            View::element("checkout/shipping_methods",null,"vivid_store");
        }
    }
    
}
