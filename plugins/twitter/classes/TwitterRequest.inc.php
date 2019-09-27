<?php
/**
 * @file plugins/generic/socialMedia/plugins/twitter/classes/TwitterRequest.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class TwitterRequest
 * @ingroup plugins_generic_socialMedia
 *
 * @brief The class provides basic functionality for requests to the Twitter API
 */

import('plugins.generic.socialMedia.plugins.twitter.classes.Util');
import('plugins.generic.socialMedia.plugins.twitter.classes.Signer');

const TWITTER_API_VERSION = '1.1';
const TWITTER_API_HOST = 'https://api.twitter.com';

abstract class TwitterRequest {
    protected $httpMethod = "POST";
    protected $httpURL;

    /**
     * Constructor
     *
     * @param $consumer TwitterConsumer
     * @param $token OAUthAccessToken
     * @param $parameters Array
     */
    function __construct(TwitterConsumer $consumer, OAUthAccessToken $token = null, $parameters = []) {
        $this->consumer = $consumer;
        $this->accessToken = $token;

        $this->parameters = $parameters;
        $this->parameters['oauth_version'] = "1.0";
        $this->parameters['oauth_nonce'] = $this->getOAuthNonce();
        $this->parameters['oauth_timestamp'] = time();
        $this->parameters['oauth_consumer_key'] = $this->consumer->key;

        if ($token != null) {
            $this->parameters['oauth_token'] = $this->accessToken->key;
        }

        $this->sign();
    }


    /**
     * Return the base string of the request
     *
     * @return string
     */
    public function getBaseString() {
        $parts = [
            $this->httpMethod,
            $this->httpURL,
            $this->getParametersToSign()
        ];

        $parts = Util::urlEncodeRfc3986($parts);
        
        return implode('&', $parts);
    }


    /**
     * Get the parameter to sign
     *
     * @return array
     */
    public function getParametersToSign() {
        $parameters = $this->parameters;

        if (isset($parameters['oauth_signature'])) {
            unset($parameters['oauth_signature']);
        }

        return Util::getHttpQuery($parameters);
    }


    /**
     * Return the authorization header
     *
     * @return string
     */
    public function getAuthorizationHeader() {
        $header = "Authorization: OAuth ";

        $parameterKeys = $this->getAuthorizationHeaderParameterKeys();

        $headerItems = [];

        foreach ($parameterKeys as $key) {
            $value = $this->parameters[$key];

            if (is_array($value)) {
                error_log("Arrays are not supported in authorization headers");
            }

            $headerItems[] = Util::urlEncodeRfc3986($key) . "=\"" . Util::urlEncodeRfc3986($value) . "\"";

        }

        $header = $header . join(", ", $headerItems);

        return $header;
    }


    /**
     * Sign the request
     */
    public function sign() {
        $signer = new Signer();
        $this->parameters["oauth_signature_method"] = $signer->getSignatureMethod();
        $signature = $signer->getSignature($this);

        $this->parameters["oauth_signature"] = $signature;
    }


    /**
     * Return tue httpURL property
     *
     * @return string
     */
    public function getURL() {
        return $this->httpURL;
    }


    /**
     * Return the oauth nonce
     *
     * @return string
     */
    protected function getOAuthNonce() {
        $baseString = base64_encode($this->generateRandomString());

        return preg_replace("/[^A-Za-z0-9 ]/", '', $baseString);
    }


    /**
     * Return a randomized string
     *
     * @param $length int
     *
     * @return string
     */
    protected function generateRandomString($length = 30) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }


    /**
     * Return the error code and message as an array
     *
     * @param $xml SimpleXMLElement Object
     *
     * @return array
     */
    protected function handleError($xml) {
        $errorCode = (int) $xml->error['code'];
        $errorMessage = (string) $xml->error[0];
        return [
            'code' => $errorCode,
            'message' => $errorMessage
        ];
    }


    /**
     * Get the keys for the parameters array for the authorization header
     *
     * @return array
     */
    abstract protected function getAuthorizationHeaderParameterKeys();
}

?>
