<?php

/*
 * This file is part of the AuthorizeNet PHP-SDK package.
 *
 * For the full copyright and license information, please view the License.pdf
 * file that was distributed with this source code.
 */

namespace AuthorizeNet\Common\Type;

/**
 * A class that contains all fields for a CIM Transaction.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCIM
 */
class Transaction
{
    public $amount;
    public $tax;
    public $shipping;
    public $duty;
    public $lineItems = array();
    public $customerProfileId;
    public $customerPaymentProfileId;
    public $customerShippingAddressId;
    public $creditCardNumberMasked;
    public $bankRoutingNumberMasked;
    public $bankAccountNumberMasked;
    public $order;
    public $taxExempt;
    public $recurringBilling;
    public $cardCode;
    public $splitTenderId;
    public $approvalCode;
    public $transId;

    public function __construct()
    {
        $this->tax = (object) array();
        $this->tax->amount = "";
        $this->tax->name = "";
        $this->tax->description = "";

        $this->shipping = (object) array();
        $this->shipping->amount = "";
        $this->shipping->name = "";
        $this->shipping->description = "";

        $this->duty = (object) array();
        $this->duty->amount = "";
        $this->duty->name = "";
        $this->duty->description = "";

        // line items

        $this->order = (object) array();
        $this->order->invoiceNumber = "";
        $this->order->description = "";
        $this->order->purchaseOrderNumber = "";
    }
}
