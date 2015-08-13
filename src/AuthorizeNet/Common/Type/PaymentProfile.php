<?php

/*
 * This file is part of the AuthorizeNet PHP-SDK package.
 *
 * For the full copyright and license information, please view the License.pdf
 * file that was distributed with this source code.
 */

namespace AuthorizeNet\Common\Type;

use AuthorizeNet\Common\Type\Address;
use AuthorizeNet\Common\Type\Payment;

/**
 * A class that contains all fields for a CIM Payment Profile.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCIM
 */
class PaymentProfile
{
    public $customerType;
    public $billTo;
    public $payment;
    public $customerPaymentProfileId;

    public function __construct()
    {
        $this->billTo = new Address();
        $this->payment = new Payment();
    }
}
