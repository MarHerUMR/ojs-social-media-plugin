<?php
/**
 * @file plugins/generic/socialMedia/plugins/twitter/classes/TwitterConsumer.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class TwitterConsumer
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Provide access to the consumer key and its secret
 */

class TwitterConsumer {
    /**
     * Constructor
     *
     * @param $key string
     * @param $secret string
     *
     */
    function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }
}

?>