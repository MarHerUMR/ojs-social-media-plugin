<?php
/**
 * @file plugins/generic/socialMedia/classes/SocialMediaPlatformSetting.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class SocialMediaPlatformSetting
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Settings object for social media platform plugins
 */

class SocialMediaPlatformSetting {
    private $_data = ["type" => "text"];

    /**
     * Constructor
     */
    function __construct($args) {
        if (!array_key_exists('id', $args)) {
            throw new Exception("The id for the SocialMediaPlatformSetting object is missing", 1);
        }

        $this->_data = array_merge($this->_data, $args);
    }

    public function __set($variable, $value) {
        $this->_data[$variable] = $value;
    }

    public function __get($variable) {
        if (isset($this->_data[$variable])) {
            return $this->_data[$variable];
        } else {
            return null;
        }
    }
}

?>
