<?php 
namespace Concrete\Package\VividStore\src\VividStore\Utilities;

use \Concrete\Core\Controller\Controller as RouteController;
use Core;
use User;
use UserInfo;
use Loader;
use Session;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Checkout extends RouteController
{
    
    //public $error;
    
    public function updater()
    {
        
        if(isset($_POST)){
            $data = $_POST;
            $e = $this->validateAddress($data);
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
        $u = new User();
        $ui = UserInfo::getByID($u->getUserID());
        $ui->setAttribute("billing_first_name",trim($data['fName']));
        Session::set('billing_first_name',trim($data['fName']));
        $ui->setAttribute("billing_last_name",trim($data['lName']));
        Session::set('billing_last_name',trim($data['lName']));
        $ui->setAttribute("billing_phone",trim($data['phone']));
        Session::set('billing_phone',trim($data['phone']));
        $address = array(
            "address1"=>trim($data['addr1']),
            "address2"=>trim($data['addr2']),
            "city"=>trim($data['city']),
            "state_province"=>trim($data['state']),
            "postal_code"=>trim($data['postal']),
            "country"=>trim($data['count']),
        );
        $ui->setAttribute("billing_address",$address);
        Session::set('billing_address',$address);
    
    }
    public function updateShipping($data)
    {
        //update the users shipping address
        $this->validateAddress($data);
        $u = new User();
        $ui = UserInfo::getByID($u->getUserID());
        $ui->setAttribute("shipping_first_name",trim($data['fName']));
        Session::set('shipping_first_name',trim($data['fName']));
        $ui->setAttribute("shipping_last_name",trim($data['lName']));
        Session::set('shipping_last_name',trim($data['lName']));
        $address = array(
            "address1"=>trim($data['addr1']),
            "address2"=>trim($data['addr2']),
            "city"=>trim($data['city']),
            "state_province"=>trim($data['state']),
            "postal_code"=>trim($data['postal']),
            "country"=>trim($data['count']),
        );
        $ui->setAttribute("shipping_address",$address);
        Session::set('shipping_address',$address);
    }
    
    public function validateAddress($data)
    {
        $e = Core::make('helper/validation/error');
        
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
            $e->add(t('You must enter a valid Zip'));
        }
        if(strlen($data['postal']) < 4){
            $e->add(t('You must enter a valid Zip'));
        }
        
        return $e;

    }
    
}