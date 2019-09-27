<?php
/**
 * @file plugins/generic/socialMedia/classes/autoposter/SocialMediaMessage.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class SocialMediaMessage
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Data object representing a social media message.
 */

class SocialMediaMessage extends DataObject {
    var $wasPosted = false;

    /**
     * Set the id
     *
     * @param id int
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Get the id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get channel ID
     *
     * @return string
     */
    function getChannelId(){
        return $this->getData('channelId');
    }


    /**
     * Set channel ID
     *
     * @param $channelId int
     */
    function setChannelId($channelId) {
        return $this->setData('channelId', $channelId);
    }


    /**
     * Get context ID
     *
     * @return string
     */
    function getContextId(){
        return $this->getData('contextId');
    }


    /**
     * Set context ID
     *
     * @param $contextId int
     */
    function setContextId($contextId) {
        return $this->setData('contextId', $contextId);
    }


    /**
     * Get value
     *
     * @return string
     */
    function getValue(){
        return $this->getData('value');
    }


    /**
     * Set value
     *
     * @param $value string
     */
    function setValue($value) {
        return $this->setData('value', $value);
    }


    /**
     * Get date added
     *
     * @return string
     */
    function getDateAdded(){
        return $this->getData('dateAdded');
    }


    /**
     * Set date added
     *
     * @param $dateAdded string
     */
    function setDateAdded($dateAdded) {
        return $this->setData('dateAdded', $dateAdded);
    }


    /**
     * Get date posted
     *
     * @param $format Format to return in. optional
     *
     * @return string
     */
    function getDatePosted($format = null){
        if ($format != null) {
            switch ($format) {
                case 'RFC3339':
                    $datetime = \DateTime::createFromFormat("Y-m-d H:i:s", $this->getData('datePosted'));
                    return $datetime->format(\DateTime::RFC3339);;
                    break;

                case 'Y-m-d':
                    $datetime = \DateTime::createFromFormat("Y-m-d H:i:s", $this->getData('datePosted'));
                    return $datetime->format("Y-m-d");;
                    break;

                default:
                    $datetime = \DateTime::createFromFormat("Y-m-d H:i:s", $this->getData('datePosted'));
                    return $datetime->format($format);
                    break;
            }
        }

        return $this->getData('datePosted');
    }


    /**
     * Set date posted
     *
     * @param $datePosted string
     */
    function setDatePosted($datePosted) {
        if ($datePosted != "0000-00-00 00:00:00") {
            $this->wasPosted = true;
        }

        return $this->setData('datePosted', $datePosted);
    }
}

?>
