<?php

/*
 * This file is part of the AuthorizeNet PHP-SDK package.
 *
 * For the full copyright and license information, please view the License.pdf
 * file that was distributed with this source code.
 */

namespace AuthorizeNet\Service\Cp;

use AuthorizeNet\Common\Response as BaseResponse;

/**
 * Parses an AuthorizeNet Card Present Response.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCP
 * @link       http://www.authorize.net/support/CP_guide.pdf Card Present Guide
 */
class Response extends BaseResponse
{
    private $_response_array = array(); // An array with the split response.

    /**
     * Constructor. Parses the AuthorizeNet response string.
     *
     * @param string $response      The response from the AuthNet server.
     * @param string $delimiter     The delimiter used (default is ",")
     * @param string $encap_char    The encap_char used (default is "|")
     * @param array  $custom_fields Any custom fields set in the request.
     */
    public function __construct($response, $delimiter, $encap_char, $custom_fields)
    {
        if ($response) {

            // If it's an XML response
            if (substr($response, 0, 5) == "<?xml") {

                $this->xml = @simplexml_load_string($response);
                // Set all fields

                // @TODO investigate
                // added to bypass "Only variables should be passed by reference"
                $explodeResult = explode('"', $response);
                $sliceResult = array_slice($explodeResult, 1,1);

                $this->version              = array_pop($sliceResult);
                $this->response_code        = (string) $this->xml->ResponseCode;

                if ($this->response_code == 1) {
                    $this->response_reason_code = (string) $this->xml->Messages->Message->Code;
                    $this->response_reason_text = (string) $this->xml->Messages->Message->Description;
                } else {
                    $this->response_reason_code = (string) $this->xml->Errors->Error->ErrorCode;
                    $this->response_reason_text = (string) $this->xml->Errors->Error->ErrorText;
                }

                $this->authorization_code   = (string) $this->xml->AuthCode;
                $this->avs_code             = (string) $this->xml->AVSResultCode;
                $this->card_code_response   = (string) $this->xml->CVVResultCode;
                $this->transaction_id       = (string) $this->xml->TransID;
                $this->md5_hash             = (string) $this->xml->TransHash;
                $this->user_ref             = (string) $this->xml->UserRef;
                $this->card_num             = (string) $this->xml->AccountNumber;
                $this->card_type            = (string) $this->xml->AccountType;
                $this->test_mode            = (string) $this->xml->TestMode;
                $this->ref_trans_id         = (string) $this->xml->RefTransID;


            } else { // If it's an NVP response

                // Split Array
                $this->response = $response;
                if ($encap_char) {
                    $this->_response_array = explode($encap_char.$delimiter.$encap_char, substr($response, 1, -1));
                } else {
                    $this->_response_array = explode($delimiter, $response);
                }

                /**
                 * If AuthorizeNet doesn't return a delimited response.
                 */
                if (count($this->_response_array) < 10) {
                    $this->approved = false;
                    $this->error = true;
                    $this->error_message = "Unrecognized response from AuthorizeNet: $response";

                    return;
                }



                // Set all fields
                $this->version              = $this->_response_array[0];
                $this->response_code        = $this->_response_array[1];
                $this->response_reason_code = $this->_response_array[2];
                $this->response_reason_text = $this->_response_array[3];
                $this->authorization_code   = $this->_response_array[4];
                $this->avs_code             = $this->_response_array[5];
                $this->card_code_response   = $this->_response_array[6];
                $this->transaction_id       = $this->_response_array[7];
                $this->md5_hash             = $this->_response_array[8];
                $this->user_ref             = $this->_response_array[9];
                $this->card_num             = $this->_response_array[20];
                $this->card_type            = $this->_response_array[21];

                // @TODO investigate
                // Looks like this fields does not come from Authorize.net server
                // $this->split_tender_id      = $this->_response_array[22];
                // $this->requested_amount     = $this->_response_array[23];
                // $this->approved_amount      = $this->_response_array[24];
                // $this->card_balance         = $this->_response_array[25];



            }
            $this->approved = ($this->response_code == self::APPROVED);
            $this->declined = ($this->response_code == self::DECLINED);
            $this->error    = ($this->response_code == self::ERROR);
            $this->held     = ($this->response_code == self::HELD);


            if ($this->error) {
                $this->error_message = "AuthorizeNet Error:
                Response Code: ".$this->response_code."
                Response Reason Code: ".$this->response_reason_code."
                Response Reason Text: ".$this->response_reason_text."
                ";
            }

        } else {
            $this->approved = false;
            $this->error = true;
            $this->error_message = "Error connecting to AuthorizeNet";
        }
    }

    /**
     * Is the MD5 provided correct?
     *
     * @param  string $api_login_id
     * @param  string $md5_setting
     * @return bool
     */
    public function isAuthorizeNet($api_login_id = false, $md5_setting = false)
    {
        $amount = ($this->amount ? $this->amount : '0.00');
        $api_login_id = ($api_login_id ? $api_login_id : AUTHORIZENET_API_LOGIN_ID);
        $md5_setting = ($md5_setting ? $md5_setting : AUTHORIZENET_MD5_SETTING);

        return ($this->md5_hash == strtoupper(md5($md5_setting . $api_login_id . $this->transaction_id . $amount)));
    }
}
