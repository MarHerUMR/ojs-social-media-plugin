<?php
/**
 * @file plugins/generic/socialMedia/plugins/twitter/classes/TwitterPostTweetRequest.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class TwitterPostTweetRequest
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Post a Tweet to the Twitter API
 */

import('plugins.generic.socialMedia.plugins.twitter.classes.Util');
import('plugins.generic.socialMedia.plugins.twitter.classes.Signer');
import('plugins.generic.socialMedia.plugins.twitter.classes.TwitterRequest');

class TwitterPostTweetRequest extends TwitterRequest {
    protected $httpMethod = "POST";
    protected $httpURL;

    /**
     * Constructor
     *
     * @copydoc TwitterRequest::_construct
     */
    function __construct(TwitterConsumer $consumer, OAUthAccessToken $token, $parameters = []) {
        parent::__construct($consumer, $token, $parameters);

        $this->httpURL = TWITTER_API_HOST . "/" . TWITTER_API_VERSION . "/statuses/update.json";

        $this->sign();
    }


    /**
     * Return the authorization header parameter keys
     *
     * @return array
     */
    public function getAuthorizationHeaderParameterKeys() {
        return [
            "oauth_consumer_key",
            "oauth_nonce",
            "oauth_signature_method",
            "oauth_token",
            "oauth_timestamp",
            "oauth_version",
            "oauth_signature"
        ];
    }
}

?>
