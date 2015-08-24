<?php

/*
 * This file is part of the AuthorizeNet PHP-SDK package.
 *
 * For the full copyright and license information, please view the License.pdf
 * file that was distributed with this source code.
 */

namespace AuthorizeNet\Common\Type;

use AuthorizeNet\Common\Type\CreditCard;
use AuthorizeNet\Common\Type\BankAccount;

/**
 * A class that contains all fields for a CIM Payment Type.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCIM
 */
class Payment
{
    public $creditCard;
    public $bankAccount;

    public function __construct()
    {
        $this->creditCard = new CreditCard();
        $this->bankAccount = new BankAccount();
    }
}
