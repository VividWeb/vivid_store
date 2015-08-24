<?php

/*
 * This file is part of the AuthorizeNet PHP-SDK package.
 *
 * For the full copyright and license information, please view the License.pdf
 * file that was distributed with this source code.
 */

namespace AuthorizeNet\Service\Cp;

use AuthorizeNet\Service\Aim\Request as AimRequest;
use AuthorizeNet\Service\Cp\Response;

/**
 * Builds and sends an AuthorizeNet CP Request.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCP
 * @link       http://www.authorize.net/support/CP_guide.pdf Card Present Guide
 */
class Request extends AimRequest
{
    const LIVE_URL = 'https://cardpresent.authorize.net/gateway/transact.dll';

    public $verify_x_fields = false;

    /**
     * Holds all the x_* name/values that will be posted in the request.
     * Default values are provided for best practice fields.
     */
    protected $_x_post_fields = array(
        "cpversion" => "1.0",
        "delim_char" => ",",
        "encap_char" => "|",
        "market_type" => "2",
        "response_format" => "1", // 0 - XML, 1 - NVP
        );

    /**
     * Device Types (x_device_type)
     * 1 = Unknown
     * 2 = Unattended Terminal
     * 3 = Self Service Terminal
     * 4 = Electronic Cash Register
     * 5 = Personal Computer- Based Terminal
     * 6 = AirPay
     * 7 = Wireless POS
     * 8 = Website
     * 9 = Dial Terminal
     * 10 = Virtual Terminal
     */

    /**
     * Strip sentinels and set track1 field.
     *
     * @param string $track1data
     */
    public function setTrack1Data($track1data)
    {
        if (preg_match('/^%.*\?$/', $track1data)) {
            $this->track1 = substr($track1data, 1, -1);
        } else {
            $this->track1 = $track1data;
        }
    }

    /**
     * Strip sentinels and set track2 field.
     *
     * @param string $track2data
     */
    public function setTrack2Data($track2data)
    {
        if (preg_match('/^;.*\?$/', $track2data)) {
            $this->track2 = substr($track2data, 1, -1);
        } else {
            $this->track2 = $track2data;
        }
    }

    /**
     *
     *
     * @param string $response
     *
     * @return AuthorizeNetAIM_Response
     */
    protected function _handleResponse($response)
    {
        return new Response($response, $this->_x_post_fields['delim_char'], $this->_x_post_fields['encap_char'], $this->_custom_fields);
    }

}
