<?php

/*
 * This file is part of the AuthorizeNet PHP-SDK package.
 *
 * For the full copyright and license information, please view the License.pdf
 * file that was distributed with this source code.
 */

namespace AuthorizeNet\Service\Cim;

use AuthorizeNet\Common\XmlResponse;
use AuthorizeNet\Service\Aim\Response as AimResponse;

/**
 * A class to parse a response from the CIM XML API.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCIM
 * @link       http://www.authorize.net/support/CIM_XML_guide.pdf CIM XML Guide
 */
class Response extends XmlResponse
{
    /**
     * @return AuthorizeNetAIM_Response
     */
    public function getTransactionResponse()
    {
        return new AimResponse($this->_getElementContents("directResponse"), ",", "", array());
    }

    /**
     * @return array Array of AuthorizeNetAIM_Response objects for each payment profile.
     */
    public function getValidationResponses()
    {
        $responses = (array) $this->xml->validationDirectResponseList;
        $return = array();
        foreach ((array) $responses["string"] as $response) {
            $return[] = new AimResponse($response, ",", "", array());
        }

        return $return;
    }

    /**
     * @return AuthorizeNetAIM_Response
     */
    public function getValidationResponse()
    {
        return new AimResponse($this->_getElementContents("validationDirectResponse"), ",", "", array());
    }

    /**
     * @return array
     */
    public function getCustomerProfileIds()
    {
        $ids = (array) $this->xml->ids;

        return $ids["numericString"];
    }

    /**
     * @return array
     */
    public function getCustomerPaymentProfileIds()
    {
        $ids = (array) $this->xml->customerPaymentProfileIdList;

        return $ids["numericString"];
    }

    /**
     * @return array
     */
    public function getCustomerShippingAddressIds()
    {
        $ids = (array) $this->xml->customerShippingAddressIdList;

        return $ids["numericString"];
    }

    /**
     * @return string
     */
    public function getCustomerAddressId()
    {
        return $this->_getElementContents("customerAddressId");
    }

    /**
     * @return string
     */
    public function getCustomerProfileId()
    {
        return $this->_getElementContents("customerProfileId");
    }

    /**
     * @return string
     */
    public function getPaymentProfileId()
    {
        return $this->_getElementContents("customerPaymentProfileId");
    }
}
