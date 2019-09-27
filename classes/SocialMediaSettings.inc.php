<?php
/**
 * @file plugins/generic/socialMedia/classes/SocialMediaSettings.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class SocialMediaSettings
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Settings object for the social media plugin
 */

class SocialMediaSettings extends DataObject {
    /**
     * Get setting by name
     *
     * @param $name string
     *
     * @return setting
     */
    function getSettingByName($name) {
        return $this->getData($name);
    }


    /**
     * Update a setting
     *
     * @param $name string
     * @param $value mixed
     * @param $type string
     */
    function updateSetting($name, $value, $type = "string") {
        switch ($type) {
            case 'string':
                $this->setData($name, $value);
                break;

            case 'boolean':
                $this->setData($name, boolval($value));
                break;

            default:
                break;
        }
    }
}

?>
