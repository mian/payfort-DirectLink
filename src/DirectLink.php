<?php

/**
 * @author Payfort
 * @copyright Copyright PayFort 2012-2015
 * @version 1.0 2015-10-11 12:36:23 PM
 */

/**
 * This class has integration methods that help you to complete for integration
 *
 * https://secure.payfort.com/Ncol/PayFort_DirectLink_EN.pdf
 * https://secure.payfort.com/Ncol/PayFort_DirectLink-3-D_EN.pdf
 */

class DirectLink {


    public $pspId; // Your affiliation name in our system
    public $orderID; // Your unique order number (merchant reference).
    public $userId; // Name of your application (API)
    public $pswd; // Password of the API user (USERID)
    public $amount; // Amount to be paid MULTIPLIED BY 100, as the format of the amount must not contain any decimals or other separators.
    public $currency; //ISO alpha order currency code, for example: EUR,USD, GBP, CHF, etc.
    public $cardNo; // Card/account number.
    public $expireDate;  //Expiry date (MM/YY or MMYY).
    public $orderDescription; // COM Order description.
    public $customerName; // Customer name.
    public $customerEmail; // Customer Email.
    public $signature; //Signature (hashed string) to authenticate the data
    public $cvc; //Card Verification Code. Depending on the card brand
    public $ownerAddress; //Customer’s street name and number.
    public $ownerZip; //Customer’s postcode..
    public $ownerTown; //Customer’s town/city name.
    public $ownerCity; //Customer’s country, e.g. BE, NL, FR, etc.
    public $ownerMobile; //Customer’s telephone number.
    public $remoteAddress; //Customer's IP address
    public $rTimeOut; //Request timeout for the transaction (in seconds)
    public $eci; /* Electronic Commerce Indicator:
                     Possible (numeric) values:
                    0 - Swiped
                    1 - Manually keyed (MOTO) (card not present)
                    2 - Recurring (from MOTO)
                    3 - Instalment payments
                    4 - Manually keyed, card present
                    7 - E-commerce with SSL encryption
                    9 - Recurring (from e-commerce)
                */
    public $alias;  //Alias
    public $operation; /*Defines the type of requested transaction. Possible values:
                                     RES: request for authorisation ,
                                     SAL: request for direct sale,
                                     RFD: refund, not linked to a previous payment,
                                    so not a maintenance operation on an existing
                                    transaction (you can not use this operation
                                    without specific permission from your acquirer).
                                    PAU: Request for pre-authorisation:*/

    public $Flag3D;  //Fixed value: ‘Y’

    public $HTTPAccept;  //The Accept request header field in the card holder browser. Accept
    public $Win3DS;  //Way to show the identification page to the customer. Possible
    public $acceptURL;  //Way to show the identification page to the customer. Possible

    /**
     * calculate fort signature
     * 
     * @param array $requestParams order request parameters
     * @param string $shaRequestPharse as Request encryption Pharse
     * @param string $securityType as security Type (sha256, sha128, sha512)
     * @return string signature
     */
    public static function calculateFortSignature($shaRequestPharse, $securityType) {
        
        $requestParams = self::getRequestParams();

        ksort($requestParams);
        $concatedStr = '';
        foreach($requestParams as $key => $value) 
        {
            if($value != ''){
                $concatedStr .= strtolower($key).'='.$value;
            }
        }

        $concatedStr = $shaRequestPharse.$concatedStr.$shaRequestPharse;
        
        if($securityType == 'sha256') {
            $signature = hash('sha256', $concatedStr);
        } elseif ($securityType == 'sha128'){
            $signature = sha1($concatedStr);
        } elseif ($securityType == 'sha512'){
            $signature = hash('sha512', $concatedStr);
        }


        return $signature;
    }
    
    /**
     * genarate array of request Params
     * 
     * @return array of $requestParams
     */
    function getRequestParams()
    {

        $requestParams =   array(
            'ACCEPTURL'            => $this->acceptURL,
            'ALIAS'            => $this->alias,
            'AMOUNT'     => $this->amount,

            'CARDNO'        => $this->cardNo,
            'CN'               => $this->customerName,
            'COM'              => $this->orderDescription,
            'CURRENCY'    => $this->currency,
            'CVC'            => $this->cvc,

            'ECI'            => $this->eci,
            'ED'           => $this->expireDate,
            'EMAIL'            => $this->customerEmail,

            'FLAG3D'            => $this->Flag3D,

            'HTTP_ACCEPT'            => $this->HTTPAccept,

            'OPERATION'            => $this->operation,
            'ORDERID'              => $this->orderID,

            'PSPID'                => $this->pspId,
            'PSWD'           => $this->pswd,

            'REMOTE_ADDR'            => $this->remoteAddress,
            'RTIMEOUT'            => $this->rTimeOut,

            'USERID'   => $this->userId,
            'WIN3DS'            => $this->Win3DS,
         //   'SHASIGN'            => $this->signature,
        );
      
       return $requestParams;
    }
    
    /**
     * redirect to fort payment page 
     * 
     * @param boolean $testMode (true, false) , if test mode is true the redirect will be to the sandBox else will be to the production
     * @param aray $requestParams order request parameters
     * @param string $action as fortm action
     */
    public static function  charge($testMode, $requestParams = array(), $action = 'POST')
    {
        if ($testMode) {
            //sandBox redirection
            $url   = 'https://secure.payfort.com/ncol/test/orderdirect.asp';
        } else {
            //production redirect
            $url = 'https://secure.payfort.com/ncol/prod/';
        }

        $requestParams = self::getRequestParams();

    }

    /**
     * Curl Request
     * @param $url
     * @param array $data
     * @return SimpleXMLElement
     * @throws Exception
     */

    public function makeRequest($url, $data = array()) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = simplexml_load_string(curl_exec($ch));
        dd($result);
        // Check for errors and such. 50001111
        $info = curl_getinfo($ch);
        $errno = curl_errno($ch);
        if($result['NCERROR']=="50001111"){
            $exception_message="Data validation error";
            throw new Exception($exception_message);
        }else if ($info['http_code'] < 200 || $info['http_code'] > 299) {
            // Got a non-200 error code.
            Start::handleErrors($result, $info['http_code']);
        }
        curl_close($ch);
        dd($result);

        return $result;
    }

    
}
