<?php

/*
 * This file is part of the AuthorizeNet PHP-SDK package.
 *
 * For the full copyright and license information, please view the License.pdf
 * file that was distributed with this source code.
 */

namespace AuthorizeNet\Common\Type;

/**
 * A class that contains all fields for a CIM Credit Card.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCIM
 */
class CreditCard
{
    public $cardNumber;
    public $expirationDate;
    public $cardCode;
}
