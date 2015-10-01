<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Order;

use Concrete\Core\Foundation\Object as Object;
use Database;
use User;
use Core;
use Package;
use Concrete\Core\Mail\Service as MailService;
use Group;
use Events;
use Config;
use Loader;
use Page;
use UserInfo;

use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as StorePrice;
use \Concrete\Package\VividStore\Src\Attribute\Key\StoreOrderKey;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use \Concrete\Package\VividStore\Src\VividStore\Tax\Tax as StoreTax;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderItem as StoreOrderItem;
use \Concrete\Package\VividStore\Src\Attribute\Value\StoreOrderValue as StoreOrderValue;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\Method as StoreShippingMethod;
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as StoreCustomer;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderEvent as StoreOrderEvent;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus\OrderStatusHistory as StoreOrderStatusHistory;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use \Concrete\Package\VividStore\Src\VividStore\Discount\DiscountCode as StoreDiscountCode;

class Order extends Object
{
    public static function getByID($oID) {
        $db = Database::get();
        $data = $db->GetRow("SELECT * FROM VividStoreOrders WHERE oID=?",$oID);
        if(!empty($data)){
            $order = new Order();
            $order->setPropertiesFromArray($data);
        }
        return($order instanceof Order) ? $order : false;
    }
    public function getCustomersMostRecentOrderByCID($cID)
    {
        $db = Database::get();
        $data = $db->GetRow("SELECT * FROM VividStoreOrders WHERE cID=? ORDER BY oID DESC",$cID);
        return Order::getByID($data['oID']);
    }
    public function add($data,$pm,$transactionReference='',$status=null)
    {
        $db = Database::get();
        
        //get who ordered it
        $customer = new StoreCustomer();
        
        //what time is it?
        $dt = Core::make('helper/date');
        $now = $dt->getLocalDateTime();
        
        //get the price details
        $smID = \Session::get('smID');
        if($smID){
            $sm = StoreShippingMethod::getByID($smID);
            $shippingMethodTypeName = $sm->getShippingMethodType()->getShippingMethodTypeName();
            $shippingMethodName = $sm->getName();
            $smName = $shippingMethodTypeName.": ".$shippingMethodName;
        } else {
            $smName = t("None");
        }
        
        $shipping = StoreCart::getShippingTotal();
        $taxes = StoreTax::getTaxes();
        $totals = StoreCart::getTotals();
        $total = $totals['total'];
        $taxCalc = Config::get('vividstore.calculation');

        $taxTotal = array();
        $taxIncludedTotal = array();
        $taxLabels = array();

        foreach($taxes as $tax){
            if ($taxCalc == 'extract') {
                $taxIncludedTotal[] = $tax['taxamount'];
            }  else {
                $taxTotal[] = $tax['taxamount'];
            }
            $taxLabels[] = $tax['name'];
        }
        
        $taxTotal = implode(',',$taxTotal);
        $taxIncludedTotal = implode(',',$taxIncludedTotal);
        $taxLabels = implode(',',$taxLabels);
        
        //get payment method
        $pmName = $pm->getPaymentMethodName();

        //add the order
        $vals = array($customer->getUserID(),$now,$pmName,$smName,$shipping,$taxTotal,$taxIncludedTotal,$taxLabels,$total);
        $db->Execute("INSERT INTO VividStoreOrders(cID,oDate,pmName,smName,oShippingTotal,oTax,oTaxIncluded,oTaxName,oTotal) VALUES (?,?,?,?,?,?,?,?,?)", $vals);
        $oID = $db->lastInsertId();
        $order = Order::getByID($oID);
        if($status){
            $order->updateStatus($status);
        } else {
            $order->updateStatus(StoreOrderStatus::getStartingStatus()->getHandle());
        }

        $email = $customer->getEmail();
        $billing_first_name = $customer->getValue("billing_first_name");
        $billing_last_name = $customer->getValue("billing_last_name");
        $billing_address = $customer->getValueArray("billing_address");
        $billing_phone = $customer->getValue("billing_phone");
        $shipping_first_name = $customer->getValue("shipping_first_name");
        $shipping_last_name = $customer->getValue("shipping_last_name");
        $shipping_address = $customer->getValueArray("shipping_address");

        $order->setAttribute("email",$email);
        $order->setAttribute("billing_first_name",$billing_first_name);
        $order->setAttribute("billing_last_name",$billing_last_name);
        $order->setAttribute("billing_address",$billing_address);
        $order->setAttribute("billing_phone",$billing_phone);

        if ($smID) {
            $order->setAttribute("shipping_first_name",$shipping_first_name);
            $order->setAttribute("shipping_last_name",$shipping_last_name);
            $order->setAttribute("shipping_address",$shipping_address);
        }


        $customer->setLastOrderID($oID);

        //add the order items
        $cart = StoreCart::getCart();
        
        foreach ($cart as $cartItem) {
            $taxes = StoreTax::getTaxForProduct($cartItem);
            
            $taxProductTotal = array();
            $taxProductIncludedTotal = array();
            $taxProductLabels = array();

            foreach($taxes as $tax){
                if ($taxCalc == 'extract') {
                    $taxProductIncludedTotal[] = $tax['taxamount'];
                }  else {
                    $taxProductTotal[] = $tax['taxamount'];
                }
                $taxProductLabels[] = $tax['name'];
            }
            $taxProductTotal = implode(',',$taxProductTotal);
            $taxProductIncludedTotal = implode(',',$taxProductIncludedTotal);
            $taxProductLabels = implode(',',$taxProductLabels);

            StoreOrderItem::add($cartItem,$oID,$taxProductTotal,$taxProductIncludedTotal,$taxProductLabels);
            
        }

        $discounts = StoreCart::getDiscounts();

        if ($discounts) {
            foreach($discounts as $discount) {

                if ($discount->requiresCode()) {
                    $order->addDiscount($discount, StoreCart::getCode());
                    if ($discount->isSingleUse()) {
                        $code = StoreDiscountCode::getByCode(StoreCart::getCode());
                        if ($code) {
                            $code->markUsed($oID);
                        }
                    }
                } else {
                    $order->addDiscount($discount);
                }
            }
        }

        //if the payment method is not external, go ahead and complete the order.
        if(!$pm->external){
            $order->completeOrder($transactionReference);
        }
                
        return $order;
    }
    public function completeOrder($transactionReference = null)
    {
        if ($transactionReference) {
            $this->setTransactionReference($transactionReference);
        }

        $smID = \Session::get('smID');
        $groupstoadd = array();
        $createlogin = false;
        $orderItems = $this->getOrderItems();
        $customer = new StoreCustomer();
        foreach($orderItems as $orderItem){
            $product = $orderItem->getProductObject();
            if ($product && $product->hasUserGroups()) {
                $groupstoadd = array_merge($groupstoadd, $product->getProductUserGroups());
            }
            if ($product && $product->createsLogin()) {
                $createlogin = true;
            }
        }
        
        if ($createlogin && $customer->isGuest()) {
            $email = $customer->getEmail();
            $user = UserInfo::getByEmail($email);

            if (!$user) {
                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);

                $mh = Loader::helper('mail');
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

                $valc = Loader::helper('concrete/validation');

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

            } else {
                // we're attempting to create a new user with an email that has already been used
                // earlier validation must have failed at this point, don't fetch the user
                $user = null;
            }

            $mh->to($email);
            $mh->sendMail();
        } elseif ($createlogin) {  // or if we found a user (because they are logged in) and need to use it to create logins
            $user = $customer->getUserInfo();
        }

         if ($user) {  // $user is going to either be the new one, or the user of the currently logged in customer

            // update the order created with the user from the newly created user
            $this->associateUser($user->getUserID());

            $billing_first_name = $customer->getValue("billing_first_name");
            $billing_last_name = $customer->getValue("billing_last_name");
            $billing_address = $customer->getValueArray("billing_address");
            $billing_phone = $customer->getValue("billing_phone");
            $shipping_first_name = $customer->getValue("shipping_first_name");
            $shipping_last_name = $customer->getValue("shipping_last_name");
            $shipping_address = $customer->getValueArray("shipping_address");

            // update the  user's attributes
            $customer = new StoreCustomer($user->getUserID());
            $customer->setValue('billing_first_name', $billing_first_name);
            $customer->setValue('billing_last_name', $billing_last_name);
            $customer->setValue('billing_address', $billing_address);
            $customer->setValue('billing_phone', $billing_phone);


            if ($smID) {
                $customer->setValue('shipping_first_name', $shipping_first_name);
                $customer->setValue('shipping_last_name', $shipping_last_name);
                $customer->setValue('shipping_address', $shipping_address);
            }

            //add user to Store Customers group
            $group = \Group::getByName('Store Customer');
            if (is_object($group) || $group->getGroupID() < 1) {
                $user->enterGroup($group);
            }

            foreach ($groupstoadd as $id) {
                $g = Group::getByID($id);
                if ($g) {
                    $user->getUserObject()->enterGroup($g);
                }
            }

            $user->refreshUserGroups();
        }
        
        StoreCart::clearCode();
        
        // create order event and dispatch
        $event = new StoreOrderEvent($this);
        Events::dispatch('on_vividstore_order', $event);
        
        //send out the alerts
        $mh = new MailService();
        $pkg = Package::getByHandle('vivid_store');

        $fromEmail = Config::get('vividstore.emailalerts');
        if(!$fromEmail){
            $fromEmail = "store@".$_SERVER['SERVER_NAME'];
        }
        $alertEmails = explode(",", Config::get('vividstore.notificationemails'));
        $alertEmails = array_map('trim',$alertEmails);
        
        //receipt
        $mh->from($fromEmail);
        $mh->to($customer->getEmail());

        $mh->addParameter("order", $this);
        $mh->load("order_receipt","vivid_store");
        $mh->sendMail();

        $validNotification = false;

        //order notification
        $mh->from($fromEmail);
        foreach ($alertEmails as $alertEmail) {
            if ($alertEmail) {
                $mh->to($alertEmail);
                $validNotification = true;
            }
        }

        if ($validNotification) {
            $mh->addParameter("order", $this);
            $mh->load("new_order_notification", "vivid_store");
            $mh->sendMail();
        }

        // unset the shipping type, as next order might be unshippable
        \Session::set('smID', '');

        StoreCart::clear();
        
        return $this;

    }
    public function remove()
    {
        $db = Database::get();
        $db->Execute("DELETE FROM VividStoreOrders WHERE oID=?",$this->oID);
        $db->Execute("DELETE FROM VividStoreOrderItems WHERE oID=?",$this->oID);
    }
    public function getOrderItems()
    {
        $db = Database::get();
        $rows = $db->GetAll("SELECT * FROM VividStoreOrderItems WHERE oID=?",$this->oID);
        $items = array();

        foreach($rows as $row){
            $items[] = StoreOrderItem::getByID($row['oiID']);
        }

        return $items;
    }
    public function getOrderID(){ return $this->oID; }
    public function getPaymentMethodName() { return $this->pmName; }
    public function getStatus(){ return $this->oStatus; }
    public function getCustomerID(){ return $this->cID; }
    public function getOrderDate(){ return $this->oDate; }
    public function getTotal() { return $this->oTotal; }
    public function getSubTotal()
    {
        $items = $this->getOrderItems();
        $subtotal = 0;
        if($items){
            foreach($items as $item){
                $subtotal = $subtotal + ($item->oiPricePaid * $item->oiQty);
            }
        }
        return $subtotal;
    }
    public function getTaxes()
    {
        $taxes = array();
        if ($this->oTax || $this->oTaxIncluded) {
            $taxAmounts = explode(",", $this->oTax);
            $taxAmountsIncluded = explode(",", $this->oTaxIncluded);
            $taxLabels = explode(",", $this->oTaxName);
            $taxes = array();
            for ($i = 0; $i < count($taxLabels); $i++) {
                $taxes[] = array(
                    'label' => $taxLabels[$i],
                    'amount' => $taxAmounts[$i],
                    'amountIncluded' => $taxAmountsIncluded[$i],
                );
            }
        }
        return $taxes;
    }
    public function getTaxTotal(){
        $taxes = $this->getTaxes();
        $taxTotal = 0;
        foreach($taxes as $tax){
            $taxTotal = $taxTotal + $tax['amount'];
        }
        return $taxTotal;
    }
    public function getIncludedTaxTotal(){
        $taxes = $this->getTaxes();
        $taxTotal = 0;
        foreach($taxes as $tax){
            $taxTotal = $taxTotal + $tax['amountIncluded'];
        }
        return $taxTotal;
    }

    public function getShippingTotal() { return $this->oShippingTotal; }
    public function getShippingMethodName(){ return $this->smName; }
    public function isShippable(){
        return ($this->smName != "");
    }
    
    public function updateStatus($status)
    {
        StoreOrderStatusHistory::updateOrderStatusHistory($this, $status);
    }
    public function getStatusHistory() {
        return StoreOrderStatusHistory::getForOrder($this);
    }
    public function setAttribute($ak, $value)
    {
        if (!is_object($ak)) {
            $ak = StoreOrderKey::getByHandle($ak);
        }
        $ak->setAttribute($this, $value);
    }
    public function getAttribute($ak, $displayMode = false) {
        if (!is_object($ak)) {
            $ak = StoreOrderKey::getByHandle($ak);
        }
        if (is_object($ak)) {
            $av = $this->getAttributeValueObject($ak);
            if (is_object($av)) {
                return $av->getValue($displayMode);
            }
        }
    }
    public function getAttributeValueObject($ak, $createIfNotFound = false) {
        $db = Database::get();
        $av = false;
        $v = array($this->getOrderID(), $ak->getAttributeKeyID());
        $avID = $db->GetOne("SELECT avID FROM VividStoreOrderAttributeValues WHERE oID = ? AND akID = ?", $v);
        if ($avID > 0) {
            $av = StoreOrderValue::getByID($avID);
            if (is_object($av)) {
                $av->setOrder($this);
                $av->setAttributeKey($ak);
            }
        }

        if ($createIfNotFound) {
            $cnt = 0;
        
            // Is this avID in use ?
            if (is_object($av)) {
                $cnt = $db->GetOne("SELECT COUNT(avID) FROM VividStoreOrderAttributeValues WHERE avID = ?", $av->getAttributeValueID());
            }
            
            if ((!is_object($av)) || ($cnt > 1)) {
                $av = $ak->addAttributeValue();
            }
        }
        
        return $av;
    }

    public function addDiscount($discount, $code = '') {
        $db = Database::get();

        //add the discount
        $vals = array($this->oID,$discount->drName, $discount->getDisplay(), $discount->drValue,$discount->drPercentage, $discount->drDeductFrom, $code);
        $db->Execute("INSERT INTO VividStoreOrderDiscounts(oID,odName,odDisplay,odValue,odPercentage,odDeductFrom,odCode) VALUES (?,?,?,?,?,?,?)", $vals);
    }

    public function getAppliedDiscounts() {
        $db = Database::get();
        $rows = $db->GetAll("SELECT * FROM VividStoreOrderDiscounts WHERE oID=?",$this->oID);
        return $rows;
    }

    public function associateUser($uID) {
        $db = Database::get();
        $rows = $db->Execute("Update VividStoreOrders set cID=? where oID = ?",array($uID, $this->oID));
        return $rows;
    }

    public function setTransactionReference($transactionReference){
        $db = Database::get();
        $db->Execute("Update VividStoreOrders set transactionReference=? where oID = ?",array($transactionReference, $this->oID));
    }

    public function getTransactionReference() {
        return $this->transactionReference;
    }
}
