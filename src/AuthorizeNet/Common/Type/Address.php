<?php

/*
 * This file is part of the AuthorizeNet PHP-SDK package.
 *
 * For the full copyright and license information, please view the License.pdf
 * file that was distributed with this source code.
 */

namespace AuthorizeNet\Common\Type;

/**
 * A class that contains all fields for a CIM Address.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCIM
 */
class Address
{
    public $firstName;
    public $lastName;
    public $company;
    public $address;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $phoneNumber;
    public $faxNumber;
    public $customerAddressId;
}
