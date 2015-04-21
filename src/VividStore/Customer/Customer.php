<?php
namespace Concrete\Package\VividStore\src\Vividstore\Customer;

use Session;
use User;

class Customer
{
    protected $ui;

    public function __construct($uID = null) {
        $u = new User();

        if ($u->isLoggedIn()) {
            $this->ui = UserInfo::getByID($u->getUserID());
        } else {
            $this->ui = null;
        }
    }

    public function getUserInfo() {
        return $this->ui;
    }

    public function setValue($handle, $value) {
        if ($this->isGuest()) {
            Session::set($handle, $value);
        } else {
            $this->ui->setAttribute($handle, $value);
        }
    }

    public function getValue($handle) {
        if ($this->isGuest()) {

           $val = Session::get($handle);

            if (is_array($val)) {
                return (object)$val;
            }

            return $val;
        } else {
            return $this->ui->getAttribute($handle);
        }
    }

    public function isGuest() {
        return is_null($this->ui);
    }

    public function getUserID(){
        if ($this->isGuest()) {
            return 0;
        } else {
            return $this->ui->getUserID();
        }
    }

    public function getEmail(){
        if ($this->isGuest()) {
            return Session::get('email');
        } else {
            return $this->ui->getUserEmail();
        }
    }

    public function setEmail($email){
        Session::set('email', $email);
    }

    public function getLastOrderID(){
        Session::get('lastOrderID');
    }

    public function setLastOrderID($id){
        Session::set('lastOrderID', $id);
    }


}