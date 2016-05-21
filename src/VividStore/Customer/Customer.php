<?php
namespace Concrete\Package\VividStore\Src\VividStore\Customer;

use Session;
use User;
use UserInfo;
use Core;
use Config;
use Page;

class Customer
{
    protected $ui;

    public function __construct($uID = null)
    {
        $u = new User();

        if ($u->isLoggedIn()) {
            $this->ui = UserInfo::getByID($u->getUserID());
        } elseif ($uID) {
            $this->ui = UserInfo::getByID($uID);
        } else {
            $this->ui = null;
        }
    }

    public function getUserInfo()
    {
        return $this->ui;
    }

    public function setValue($handle, $value)
    {
        if ($this->isGuest()) {
            Session::set('vivid_' . $handle, $value);
        } else {
            $this->getUserInfo()->setAttribute($handle, $value);
        }
    }

    public function getValue($handle)
    {
        if ($this->isGuest()) {
            $val = Session::get('vivid_' .$handle);

            if (is_array($val)) {
                return (object)$val;
            }

            return $val;
        } else {
            return $this->ui->getAttribute($handle);
        }
    }

    public function getValueArray($handle)
    {
        if ($this->isGuest()) {
            $val = Session::get('vivid_' .$handle);
            return $val;
        } else {
            return $this->ui->getAttribute($handle);
        }
    }

    public function isGuest()
    {
        return is_null($this->ui);
    }

    public function getUserID()
    {
        if ($this->isGuest()) {
            return 0;
        } else {
            return $this->ui->getUserID();
        }
    }

    public function getEmail()
    {
        if ($this->isGuest()) {
            return Session::get('vivid_email');
        } else {
            return $this->ui->getUserEmail();
        }
    }

    public function setEmail($email)
    {
        Session::set('vivid_email', $email);
    }

    public function getLastOrderID()
    {
        return Session::get('vivid_lastOrderID');
    }

    public function setLastOrderID($id)
    {
        Session::set('vivid_lastOrderID', $id);
    }

    public function createCustomer()
    {
        $customer = new self();
        if ($customer->isGuest()) {
            $email = $customer->getEmail();
            $user = UserInfo::getByEmail($email);

            if (!$user) {
                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
                $mh = Core::make('helper/mail');
                $mh->addParameter('siteName', Config::get('concrete.site'));
                $navhelper = Core::make('helper/navigation');
                $target = Page::getByPath('/login');
                if ($target) {
                    $link = $navhelper->getLinkToCollection($target, true);
                    if ($link) {
                        $mh->addParameter('link', $link);
                    }
                } else {
                    $mh->addParameter('link', '');
                }
                $valc = Core::make('helper/concrete/validation');
                $min = Config::get('concrete.user.username.minimum');
                $max = Config::get('concrete.user.username.maximum');
                $newusername = preg_replace("/[^A-Za-z0-9_]/", '', strstr($email, '@', true));
                while (!$valc->isUniqueUsername($newusername) || strlen($newusername) < $min) {
                    if (strlen($newusername) >= $max) {
                        $newusername = substr($newusername, 0, $max - 5);
                    }
                    $newusername .= rand(0, 9);
                }
                $user = UserInfo::add(array('uName' => $newusername, 'uEmail' => trim($email), 'uPassword' => $password));
                if (Config::get('concrete.user.registration.email_registration')) {
                    $mh->addParameter('username', trim($email));
                } else {
                    $mh->addParameter('username', $newusername);
                }

                $mh->addParameter('password', $password);
                $email = trim($email);

                $mh->load('new_user', 'vivid_store');

                // login the newly created user
                User::loginByUserID($user->getUserID());

                // new user password email
                $fromEmail = Config::get('vividstore.emailalerts');
                if (!$fromEmail) {
                    $fromEmail = "store@" . $_SERVER['SERVER_NAME'];
                }
                $fromName = Config::get('vividstore.emailalertsname');
                if ($fromName) {
                    $mh->from($fromEmail, $fromName);
                } else {
                    $mh->from($fromEmail);
                }

                $mh->to($email);
                $mh->sendMail();
            } else {
                // we're attempting to create a new user with an email that has already been used
                // earlier validation must have failed at this point, don't fetch the user
                $user = null;
            }
        } else {
            $user = $customer->getUserInfo();
        }
        return $user;
    }

    public static function addCustomerToUserGroupsByOrder($order)
    {
        $groups = array();
        $customer = new Customer();
        $orderItems = $order->getOrderItems();
        foreach ($orderItems as $orderItem) {
            $product = $orderItem->getProductObject();
            if ($product && $product->hasUserGroups()) {
                $productUserGroups = $product->getProductUserGroups();

                foreach ($productUserGroups as $pug) {
                    $groups[] = \Group::getByID($pug->getUserGroupID());
                }
            }
        }
        //and of course, add the user to the generic group.
        $groups[] = \Group::getByName('Store Customer');
        foreach ($groups as $groupObject) {
            if (is_object($groupObject)) {
                $customer->getUserInfo()->getUserObject()->enterGroup($groupObject);
            }
        }
    }
}
