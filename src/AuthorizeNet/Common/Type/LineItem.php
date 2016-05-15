<?php

/*
 * This file is part of the AuthorizeNet PHP-SDK package.
 *
 * For the full copyright and license information, please view the License.pdf
 * file that was distributed with this source code.
 */

namespace AuthorizeNet\Common\Type;

/**
 * A class that contains all fields for a CIM Transaction Line Item.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCIM
 */
class LineItem
{
    public $itemId;
    public $name;
    public $description;
    public $quantity;
    public $unitPrice;
    public $taxable;
}
