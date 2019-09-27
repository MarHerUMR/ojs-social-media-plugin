<?php
/**
 * @file plugins/generic/socialMedia/plugins/twitter/classes/Signer.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class Signer
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Signs a request with a given signature method
 */

class Signer {
    /**
     * Constructor
     */
    function __construct() {
        $this->signatureMethod = "HMAC-SHA1";
    }


    /**
     * Return the signature
     *
     * @param $request A TwitterRequest subclass
     *
     * @return string
     */
    public function getSignature($request) {
        $signature = null;
        
        $parameters = $request->getParametersToSign();

        if ($this->getSignatureMethod() == "HMAC-SHA1") {
            $baseString = $request->getBaseString();
            $compositeKey = rawurlencode($request->consumer->secret) . '&';

            // When the request has an access token, use the access token secret as well for the signature
            if ($request->accessToken != null) {
                $compositeKey .= rawurlencode($request->accessToken->secret);
            }

            $signature = base64_encode(hash_hmac('sha1', $baseString, $compositeKey, true));
        }

        return $signature;
    }


    /**
     * Return the signature method
     *
     * @return string
     */
    public function getSignatureMethod() {
        return $this->signatureMethod;
    }
}

?>
