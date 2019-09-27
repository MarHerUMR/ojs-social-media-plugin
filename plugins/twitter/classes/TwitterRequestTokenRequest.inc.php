<?php
/**
 * @file plugins/generic/socialMedia/plugins/twitter/classes/TwitterRequestTokenRequest.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class TwitterRequestTokenRequest
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Fetch a request token from the Twitter API
 */

import('plugins.generic.socialMedia.plugins.twitter.classes.Util');
import('plugins.generic.socialMedia.plugins.twitter.classes.Signer');
import('plugins.generic.socialMedia.plugins.twitter.classes.TwitterRequest');

class TwitterRequestTokenRequest extends TwitterRequest {
    protected $httpMethod = "POST";
    protected $httpURL = "https://twitter.com/oauth/request_token";

    /**
     * @copydoc TwitterRequest::_construct
     */
    function __construct(TwitterConsumer $consumer, $parameters = []) {
        parent::__construct($consumer, null, $parameters);

        $this->sign();
    }


    /**
     * Return the authorization header parameter keys
     *
     * @return array
     */
    public function getAuthorizationHeaderParameterKeys() {
        return [
            "oauth_nonce",
            "oauth_callback",
            "oauth_signature_method",
            "oauth_timestamp",
            "oauth_consumer_key",
            "oauth_signature",
            "oauth_version"
        ];
   }


   /**
    * Make a curl request to fetch a request token and return the token
    *
    * @return string
    */
   public function makeRequest() {
        $options = [
            CURLOPT_URL => $this->httpURL,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [$this->getAuthorizationHeader(), 'Expect:'],
            CURLOPT_POSTFIELDS => ""
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $response = "";
        $response = curl_exec($curl);

        if (curl_errno($curl) > 0) {
            // TODO: Add meaningfull error message
            error_log("curl connection failed.");
        }

        // Check if the request succeeded
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($statusCode == 200) {
            return $response;
        } elseif ($statusCode == 403){
            if (curl_getinfo($curl, CURLINFO_CONTENT_TYPE) == "application/xml;charset=utf-8") {

                $xml=simplexml_load_string($response);

                if ($xml->getName() == "errors") {
                    return $this->handleError($xml);
                }
            }

            return "error";
        }

        return false;
   }


   /**
    * Return the url where the user should be redirected to in order to grant access to the Twitter account
    *
    * @return string
    */
   public function getRedirectURL() {
       return "https://api.twitter.com/oauth/authorize?";
   }
}

?>
