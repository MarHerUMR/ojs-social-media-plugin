<?php
/**
 * @file plugins/generic/socialMedia/plugins/twitter/classes/OAuthAccessToken.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class OAuthAccessToken
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Provide the oauth access token data
 */

import('plugins.generic.socialMedia.plugins.twitter.classes.Util');

class OAuthAccessToken {
    public $key;
    public $secret;

    /**
     * Constructor
     *
     * @param $key
     * @param $secret
     *
     */
    function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }


    public function __toString() {
        return implode([
            "oauth_token=",
            Util::urlEncodeRfc3986($this->key),
            "&oauth_token_secret=",
            Util::urlEncodeRfc3986($this->secret)
        ]);
    }
}

?>
