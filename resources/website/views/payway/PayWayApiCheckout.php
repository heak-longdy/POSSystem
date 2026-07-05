<?php
/*
|--------------------------------------------------------------------------
| ABA PayWay API URL
|--------------------------------------------------------------------------
| API URL that is provided by PayWay must be required in your post form
|
*/
define('ABA_PAYWAY_API_URL', 'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/purchase');

/*
|--------------------------------------------------------------------------
| ABA PayWay API KEY
|--------------------------------------------------------------------------
| API KEY that is generated and provided by PayWay must be required in your post form
|
*/
define('ABA_PAYWAY_API_KEY', 'aa2f5d25-3667-43b0-9d21-3169ebc135a5');

/*
|--------------------------------------------------------------------------
| ABA PayWay Merchant ID
|--------------------------------------------------------------------------
| Merchant ID that is generated and provided by PayWay must be required in your post form
|
*/
define('ABA_PAYWAY_MERCHANT_ID', 'simplebarber');


class PayWayApiCheckout {

    /**
     * Returns the getHash
     * For PayWay security, you must follow the way of encryption for hash.
     *
     * @param string $transactionId
     * @param string $amount
     *
     * @return string getHash
     */
    public static function getHash($str) {
        $hash = base64_encode(hash_hmac('sha512', $str, ABA_PAYWAY_API_KEY, true));
        return $hash;
    }

    /**
     * Returns the getApiUrl
     *
     * @return string getApiUrl
     */
    public static function getApiUrl() {
        return ABA_PAYWAY_API_URL;
    }
}
