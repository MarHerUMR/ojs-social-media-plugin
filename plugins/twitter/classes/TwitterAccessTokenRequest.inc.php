<?php
/**
 * @file plugins/generic/socialMedia/plugins/twitter/classes/TwitterAccessTokenRequest.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class TwitterAccessTokenRequest
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Converting the request token to an access token.
 */

import('plugins.generic.socialMedia.plugins.twitter.classes.Util');
import('plugins.generic.socialMedia.plugins.twitter.classes.Signer');
import('plugins.generic.socialMedia.plugins.twitter.classes.TwitterRequest');

class TwitterAccessTokenRequest extends TwitterRequest {
    protected $httpMethod = "POST";
    protected $httpURL = "https://api.twitter.com/oauth/access_token";

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
            "oauth_token",
            "oauth_verifier",
            "oauth_signature_method",
            "oauth_timestamp",
            "oauth_consumer_key",
            "oauth_signature",
            "oauth_version"
        ];
   }


   /**
    * Make the request to the Twitter API
    */
   public function makeRequest() {
        $authHeader = $this->getAuthorizationHeader();
        $options = [
            CURLOPT_URL => $this->httpURL,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [$authHeader, 'Expect:'],
            CURLOPT_POSTFIELDS => ""
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $response = "";
        $response = curl_exec($curl);

        if (curl_errno($curl) > 0) {
            error_log("curl connection to fetch TwitterAccessToken failed.");
            error_log(sprintf("Error: %s", curl_error($curl)));
        }

        // Check if request succeeded
        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
            return $response;
        } else {
            error_log(sprintf("CURL HTTP STATUS CODE: %s", curl_getinfo($curl, CURLINFO_HTTP_CODE)));
            error_log(sprintf("response: %s", print_r($response, true)));
        }

        return false;
   }

}

?>
