<?php

/**
 * Class to validate callbacks from Coinify
 *
 * Class CoinifyCallback
 */
class CoinifyCallback {

    /**
     * Coinify IPN callback secret. Get yours at https://www.coinify.com/merchant/ipn
     *
     * @var string
     */
    private $ipn_secret;

    public function __construct( $ipn_secret ) {
        $this->ipn_secret = $ipn_secret;
    }

    /**
     * Validates a callback and it's signature based on the IPN secret given in the constructor.
     *
     * @param string $callback_raw The raw JSON POST data sent with the callback (before JSON decoding)
     * @param null|string $signature The signature as extracted from the 'X-Coinify-Callback-Signature' HTTP header.
     * Must be a 64-byte hexadecimal string. If $signature is set to null, fetch it automatically from
     * $_SERVER['HTTP_X_COINIFY_CALLBACK_SIGNATURE']
     *
     * @return bool Whether the callback was valid or not
     */
    public function validateCallback( $callback_raw, $signature=null ) {
        if ( $signature == null && isset( $_SERVER['HTTP_X_COINIFY_CALLBACK_SIGNATURE'] ) ) {
            $signature = $_SERVER['HTTP_X_COINIFY_CALLBACK_SIGNATURE'];
        }

        // Calculate the signature using the callback data and your IPN secret
        $expected_signature = strtolower( hash_hmac('sha256', $callback_raw, $this->ipn_secret, false) );

        // Check that the signatures match
        if ( strtolower( $signature ) != $expected_signature ) {
            // Invalid signature, disregard this callback
            return false;
        }

        // Valid signature
        return true;
    }

}