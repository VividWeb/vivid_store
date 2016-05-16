<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Order;

use Database;
use User;
use Concrete\Core\Mail\Service as MailService;
use Events;
use Config;
use UserInfo;
use Doctrine\Common\Collections\ArrayCollection;
use \Concrete\Package\VividStore\Src\Attribute\Key\StoreOrderKey;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use \Concrete\Package\VividStore\Src\VividStore\Tax\Tax as StoreTax;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderItem as StoreOrderItem;
use \Concrete\Package\VividStore\Src\Attribute\Value\StoreOrderValue as StoreOrderValue;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethod as StoreShippingMethod;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderEvent as StoreOrderEvent;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus\OrderStatusHistory as StoreOrderStatusHistory;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductFile as StoreProductFile;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as StorePaymentMethod;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Calculator as StoreCalculator;
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as StoreCustomer;

/**
 * @Entity
 * @Table(name="VividStoreOrders")
 */
class Order
{
    /** 
     * @Id @Column(type="integer", options={"unsigned"=true})
     * @GeneratedValue 
     */
    protected $oID;
    
    /** @Column(type="integer",nullable=true) */
    protected $cID;
    
    /** @Column(type="datetime") */
    protected $oDate;

    /** @Column(type="text") */
    protected $pmName;
    
    /** @Column(type="text") */
    protected $smName;
   
    /** @Column(type="decimal", precision=10, scale=2) **/
    protected $oShippingTotal;

    /** @Column(type="text", nullable=true) **/
    protected $oTax;
    
    /** @Column(type="text", nullable=true) **/
    protected $oTaxIncluded;

    /** @Column(type="text", nullable=true) **/
    protected $oTaxName;
    
    /** @Column(type="decimal", precision=10, scale=2) **/
    protected $oTotal;

    /** @Column(type="text", nullable=true) */
    protected $transactionReference;

    /**
     * @OneToMany(targetEntity="Concrete\Package\VividStore\Src\VividStore\Order\OrderItem", mappedBy="order",cascade={"persist"}))
     */
    protected $orderItems;

    public function setCustomerID($cID)
    {
        $this->cID = $cID;
    }
    public function setOrderDate($oDate)
    {
        $this->oDate = $oDate;
    }
    public function setPaymentMethodName($pmName)
    {
        $this->pmName = $pmName;
    }
    public function setShippingMethodName($smName)
    {
        $this->smName = $smName;
    }
    public function setShippingTotal($shippingTotal)
    {
        $this->oShippingTotal = $shippingTotal;
    }
    public function setTaxTotals($taxTotal)
    {
        $this->oTax = $taxTotal;
    }
    public function setTaxIncluded($taxIncluded)
    {
        $this->oTaxIncluded = $taxIncluded;
    }
    public function setTaxLabels($taxLabels)
    {
        $this->oTaxName = $taxLabels;
    }
    public function setOrderTotal($total)
    {
        $this->oTotal = $total;
    }
    public function setTransactionReference($transactionReference)
    {
        $this->transactionReference = $transactionReference;
    }
    public function saveTransactionReference($transactionReference)
    {
        $this->setTransactionReference($transactionReference);
        $this->save();
    }

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }
    public function getOrderID()
    {
        return $this->oID;
    }
    public function getOrderItems()
    {
        return $this->orderItems;
    }
    public function getCustomerID()
    {
        return $this->cID;
    }
    public function getOrderDate()
    {
        return $this->oDate;
    }
    public function getPaymentMethodName()
    {
        return $this->pmName;
    }
    public function getShippingMethodName()
    {
        return $this->smName;
    }
    public function getShippingTotal()
    {
        return $this->oShippingTotal;
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

    public function getTaxTotal()
    {
        $taxes = $this->getTaxes();
        $taxTotal = 0;
        foreach ($taxes as $tax) {
            $taxTotal = $taxTotal + $tax['amount'];
        }
        return $taxTotal;
    }

    public function getIncludedTaxTotal()
    {
        $taxes = $this->getTaxes();
        $taxTotal = 0;
        foreach ($taxes as $tax) {
            $taxTotal = $taxTotal + $tax['amountIncluded'];
        }
        return $taxTotal;
    }

    public function getTotal()
    {
        return $this->oTotal;
    }

    public function getSubTotal()
    {
        $items = $this->getOrderItems();
        $subtotal = 0;
        if ($items) {
            foreach ($items as $item) {
                $subtotal = $subtotal + ($item->getPricePaid() * $item->getQty());
            }
        }

        return $subtotal;
    }
    public function getTransactionReference()
    {
        return $this->transactionReference;
    }
    
    public static function getByID($oID)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $oID);
    }

    public function getCustomersMostRecentOrderByCID($cID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->getRepository(get_class())->findOneBy(array('cID' => $cID));
    }

    /**
     * @param array $data
     * @param StorePaymentMethod $pm
     * @param string $transactionReference
     * @param boolean $status
     * @return Order
     */
    public function add($data, $pm, $transactionReference='', $status=null)
    {
        $customer = new StoreCustomer();
        $now = new \DateTime;
        $smName = StoreShippingMethod::getActiveShippingMethodName();
        $shippingTotal = StoreCalculator::getShippingTotal();
        $taxes = StoreTax::getConcatenatedTaxStrings();
        $totals = StoreCalculator::getTotals();
        $total = $totals['total'];
        $pmName = $pm->getPaymentMethodName();

        $order = new Order();
        $order->setCustomerID($customer->getUserID());
        $order->setOrderDate($now);
        $order->setPaymentMethodName($pmName);
        $order->setShippingMethodName($smName);
        $order->setShippingTotal($shippingTotal);
        $order->setTaxTotals($taxes['taxTotals']);
        $order->setTaxIncluded($taxes['taxIncludedTotal']);
        $order->setTaxLabels($taxes['taxLabels']);
        $order->setOrderTotal($total);
        $order->save();

        $customer->setLastOrderID($order->getOrderID());
        $order->updateStatus($status);
        $order->addCustomerAddress($customer, $order->isShippable());
        $order->addOrderItems(StoreCart::getCart());
        $order->createNeededAccounts();
        $order->assignFilePermissions();
        if (!$pm->getMethodController()->external) {
            $order->completeOrder($transactionReference);
        }
        return $order;
    }


    /**
     * @param StoreCustomer $customer
     * @param bool $includeShipping
     */
    public function addCustomerAddress($customer=null, $includeShipping=true)
    {
        if (!$customer instanceof StoreCustomer) {
            $customer = new StoreCustomer();
        }
        $email = $customer->getEmail();
        $billing_first_name = $customer->getValue("billing_first_name");
        $billing_last_name = $customer->getValue("billing_last_name");
        $billing_address = $customer->getValueArray("billing_address");
        $billing_phone = $customer->getValue("billing_phone");
        $shipping_first_name = $customer->getValue("shipping_first_name");
        $shipping_last_name = $customer->getValue("shipping_last_name");
        $shipping_address = $customer->getValueArray("shipping_address");

        $this->setAttribute("email", $email);
        $this->setAttribute("billing_first_name", $billing_first_name);
        $this->setAttribute("billing_last_name", $billing_last_name);
        $this->setAttribute("billing_address", $billing_address);
        $this->setAttribute("billing_phone", $billing_phone);
        if ($includeShipping) {
            $this->setAttribute("shipping_first_name", $shipping_first_name);
            $this->setAttribute("shipping_last_name", $shipping_last_name);
            $this->setAttribute("shipping_address", $shipping_address);
        }
    }

    public function addOrderItems($cart)
    {
        $taxCalc = Config::get('vividstore.calculation');
        foreach ($cart as $cartItem) {
            $taxes = StoreTax::getTaxForProduct($cartItem);
            $taxProductTotal = array();
            $taxProductIncludedTotal = array();
            $taxProductLabels = array();

            foreach ($taxes as $tax) {
                if ($taxCalc == 'extract') {
                    $taxProductIncludedTotal[] = $tax['taxamount'];
                } else {
                    $taxProductTotal[] = $tax['taxamount'];
                }
                $taxProductLabels[] = $tax['name'];
            }
            $taxProductTotal = implode(',', $taxProductTotal);
            $taxProductIncludedTotal = implode(',', $taxProductIncludedTotal);
            $taxProductLabels = implode(',', $taxProductLabels);

            $orderItem = StoreOrderItem::add($cartItem, $this->getOrderID(), $taxProductTotal, $taxProductIncludedTotal, $taxProductLabels);
            $this->orderItems->add($orderItem);
        }
    }

    public function save()
    {
        $em = \Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    public function delete()
    {
        $this->getShippingMethodTypeMethod()->delete();
        $em = \Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }

    private function assignFilePermissions()
    {
        foreach ($this->getOrderItems() as $orderItem) {
            $product = $orderItem->getProductObject();
            if ($product->hasDigitalDownload()) {
                $fileObjs = StoreProductFile::getFileObjectsForProduct($product);
                $fileObj = $fileObjs[0];
                $pk = \Concrete\Core\Permission\Key\FileKey::getByHandle('view_file');
                $pk->setPermissionObject($fileObj);
                $pao = $pk->getPermissionAssignmentObject();
                $u = new User();
                $uID = $u->getUserID();
                $ui = UserInfo::getByID($uID);
                $user = \Concrete\Core\Permission\Access\Entity\UserEntity::getOrCreate($ui);
                $pa = $pk->getPermissionAccessObject();
                if ($pa) {
                    $pa->addListItem($user);
                    $pao->assignPermissionAccess($pa);
                }
            }
        }
    }

    public function createNeededAccounts()
    {
        $createAccount = false;
        $orderItems = $this->getOrderItems();
        foreach ($orderItems as $orderItem) {
            $product = $orderItem->getProductObject();
            if ($product && $product->createsLogin()) {
                $createAccount = true;
            }
        }
        if ($createAccount) {
            $customer = StoreCustomer::createCustomer();
            $this->setCustomerID($customer->getCustomerID());
        }
    }

    public function completeOrder($transactionReference = null)
    {
        if ($transactionReference) {
            $this->setTransactionReference($transactionReference);
            $this->save();
        }
        //in case of external payment hitting, update the status.
        //otherwise nothing should really happen.
        $this->updateStatus();
        $this->dispatchEmailNotifications();
        StoreCustomer::addCustomerToUserGroupsByOrder($this);

        $event = new StoreOrderEvent($this);
        Events::dispatch('on_vividstore_order', $event);

        // unset the shipping type, as next order might be unshippable
        \Session::set('smID', '');

        StoreCart::clear();
        
        return $this;
    }
    public function dispatchEmailNotifications()
    {
        $fromEmail = Config::get('vividstore.emailalerts');
        if (!$fromEmail) {
            $fromEmail = "store@" . $_SERVER['SERVER_NAME'];
        }
        $fromName = Config::get('vividstore.emailalertsname');

        $mh = new MailService();

        $alertEmails = explode(",", Config::get('vividstore.notificationemails'));
        $alertEmails = array_map('trim', $alertEmails);

        //receipt
        $customer = new StoreCustomer();
        $mh->from($fromEmail, $fromName?$fromName:null);
        $mh->to($customer->getEmail());
        $mh->addParameter("order", $this);
        $mh->load("order_receipt", "vivid_store");
        $mh->sendMail();

        $validNotification = false;

        //order notification
        $mh->from($fromEmail, $fromName?$fromName:null);
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
    }

    public function remove()
    {
        StoreOrderItem::removeOrderItemsByOrder($this);
        $this->delete();
    }

    public function isShippable()
    {
        return $this->getShippingMethodName() != "";
    }

    public function updateStatus($status=null)
    {
        if ($status) {
            StoreOrderStatusHistory::updateOrderStatusHistory($this, $status);
        } else {
            StoreOrderStatusHistory::updateOrderStatusHistory($this, StoreOrderStatus::getStartingStatus()->getHandle());
        }
    }

    public function getStatusHistory()
    {
        return StoreOrderStatusHistory::getForOrder($this);
    }

    public function getStatus()
    {
        $history = StoreOrderStatusHistory::getForOrder($this);

        if (!empty($history)) {
            $laststatus = $history[0];
            return $laststatus->getOrderStatusName();
        } else {
            return '';
        }
    }

    public function getStatusHandle()
    {
        $history = StoreOrderStatusHistory::getForOrder($this);

        if (!empty($history)) {
            $laststatus = $history[0];

            return $laststatus->getOrderStatusHandle();
        } else {
            return '';
        }
    }

    public function setAttribute($ak, $value)
    {
        if (!is_object($ak)) {
            $ak = StoreOrderKey::getByHandle($ak);
        }
        $ak->setAttribute($this, $value);
    }

    public function getAttribute($ak, $displayMode = false)
    {
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

    public function getAttributeValueObject($ak, $createIfNotFound = false)
    {
        $db = \Database::connection();
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

    public function addDiscount($discount, $code = '')
    {
        $db = \Database::connection();

        //add the discount
        $vals = array($this->oID,$discount->drName, $discount->getDisplay(), $discount->drValue,$discount->drPercentage, $discount->drDeductFrom, $code);
        $db->Execute("INSERT INTO VividStoreOrderDiscounts(oID,odName,odDisplay,odValue,odPercentage,odDeductFrom,odCode) VALUES (?,?,?,?,?,?,?)", $vals);
    }

    public function getAppliedDiscounts()
    {
        $db = \Database::connection();
        $rows = $db->GetAll("SELECT * FROM VividStoreOrderDiscounts WHERE oID=?", $this->oID);
        return $rows;
    }

    public function associateUser($cID)
    {
        $this->setCustomerID($cID);
        $this->save();
    }
}
