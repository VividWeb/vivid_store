<?php

/*
 * This file is part of the AuthorizeNet PHP-SDK package.
 *
 * For the full copyright and license information, please view the License.pdf
 * file that was distributed with this source code.
 */

namespace AuthorizeNet\Common\Type;

/**
 * A class that contains all fields for an AuthorizeNet ARB Subscription.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetARB
 */
class Subscription
{
    public $name;
    public $intervalLength;
    public $intervalUnit;
    public $startDate;
    public $totalOccurrences;
    public $trialOccurrences;
    public $amount;
    public $trialAmount;
    public $creditCardCardNumber;
    public $creditCardExpirationDate;
    public $creditCardCardCode;
    public $bankAccountAccountType;
    public $bankAccountRoutingNumber;
    public $bankAccountAccountNumber;
    public $bankAccountNameOnAccount;
    public $bankAccountEcheckType;
    public $bankAccountBankName;
    public $orderInvoiceNumber;
    public $orderDescription;
    public $customerId;
    public $customerEmail;
    public $customerPhoneNumber;
    public $customerFaxNumber;
    public $billToFirstName;
    public $billToLastName;
    public $billToCompany;
    public $billToAddress;
    public $billToCity;
    public $billToState;
    public $billToZip;
    public $billToCountry;
    public $shipToFirstName;
    public $shipToLastName;
    public $shipToCompany;
    public $shipToAddress;
    public $shipToCity;
    public $shipToState;
    public $shipToZip;
    public $shipToCountry;

    public function getXml()
    {
        $xml = "<subscription>
    <name>{$this->name}</name>
    <paymentSchedule>
        <interval>
            <length>{$this->intervalLength}</length>
            <unit>{$this->intervalUnit}</unit>
        </interval>
        <startDate>{$this->startDate}</startDate>
        <totalOccurrences>{$this->totalOccurrences}</totalOccurrences>
        <trialOccurrences>{$this->trialOccurrences}</trialOccurrences>
    </paymentSchedule>
    <amount>{$this->amount}</amount>
    <trialAmount>{$this->trialAmount}</trialAmount>
    <payment>
        <creditCard>
            <cardNumber>{$this->creditCardCardNumber}</cardNumber>
            <expirationDate>{$this->creditCardExpirationDate}</expirationDate>
            <cardCode>{$this->creditCardCardCode}</cardCode>
        </creditCard>
        <bankAccount>
            <accountType>{$this->bankAccountAccountType}</accountType>
            <routingNumber>{$this->bankAccountRoutingNumber}</routingNumber>
            <accountNumber>{$this->bankAccountAccountNumber}</accountNumber>
            <nameOnAccount>{$this->bankAccountNameOnAccount}</nameOnAccount>
            <echeckType>{$this->bankAccountEcheckType}</echeckType>
            <bankName>{$this->bankAccountBankName}</bankName>
        </bankAccount>
    </payment>
    <order>
        <invoiceNumber>{$this->orderInvoiceNumber}</invoiceNumber>
        <description>{$this->orderDescription}</description>
    </order>
    <customer>
        <id>{$this->customerId}</id>
        <email>{$this->customerEmail}</email>
        <phoneNumber>{$this->customerPhoneNumber}</phoneNumber>
        <faxNumber>{$this->customerFaxNumber}</faxNumber>
    </customer>
    <billTo>
        <firstName>{$this->billToFirstName}</firstName>
        <lastName>{$this->billToLastName}</lastName>
        <company>{$this->billToCompany}</company>
        <address>{$this->billToAddress}</address>
        <city>{$this->billToCity}</city>
        <state>{$this->billToState}</state>
        <zip>{$this->billToZip}</zip>
        <country>{$this->billToCountry}</country>
    </billTo>
    <shipTo>
        <firstName>{$this->shipToFirstName}</firstName>
        <lastName>{$this->shipToLastName}</lastName>
        <company>{$this->shipToCompany}</company>
        <address>{$this->shipToAddress}</address>
        <city>{$this->shipToCity}</city>
        <state>{$this->shipToState}</state>
        <zip>{$this->shipToZip}</zip>
        <country>{$this->shipToCountry}</country>
    </shipTo>
</subscription>";

        $xml_clean = "";
        // Remove any blank child elements
        foreach (preg_split("/(\r?\n)/", $xml) as $key => $line) {
            if (!preg_match('/><\//', $line)) {
                $xml_clean .= $line . "\n";
            }
        }

        // Remove any blank parent elements
        $element_removed = 1;
        // Recursively repeat if a change is made
        while ($element_removed) {
            $element_removed = 0;
            if (preg_match('/<[a-z]+>[\r?\n]+\s*<\/[a-z]+>/i', $xml_clean)) {
                $xml_clean = preg_replace('/<[a-z]+>[\r?\n]+\s*<\/[a-z]+>/i', '', $xml_clean);
                $element_removed = 1;
            }
        }

        // Remove any blank lines
        // $xml_clean = preg_replace('/\r\n[\s]+\r\n/','',$xml_clean);
        return $xml_clean;
    }
}
