<?php
/**
 * @file plugins/generic/socialMedia/classes/autoposter/PostingChannel.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class PostingChannel
 * @ingroup PostingChannel
 *
 * @brief Class for a posting channel
 */

import('plugins.generic.socialMedia.classes.autoposter.MessageQueue');

class PostingChannel extends DataObject {
    private $messageLog = array();
    var $_messagePoster = null;
    var $_messageQueue = null;


    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
    }


    /**
     * Set the context id of the posting channel
     *
     * @param $contextId int
     */
    function setContextId($contextId) {
        $this->contextId = $contextId;
    }


    /**
     * Get the posting frequency
     *
     * @return array
     */
    function getFrequency() {
        return [$this->getData("frequencyAmount"), $this->getData("frequencyUnit")];
    }


    /**
     * Get the posting frequency as string
     *
     * @return string
     */
    function getFrequencyString() {
        return $this->getData('frequency');
    }


    /**
     * Set posting frequency
     *
     * @param $frequency string
     */
    function setFrequency($frequency) {
        // Check validity
        $validUnits = array("D", "H", "M");
        $matches = null;

        if(!preg_match("/(\d*)(D|H|M)/", $frequency, $matches)) {
            error_log(sprintf("%s is not a valid frequency", $frequency));
        } else {
            $this->setData("frequencyAmount", $matches[1]);
            $this->setData("frequencyUnit", $matches[2]);
            $this->setData("frequency", $frequency);
        }
    }


    /**
     * Return wether the posting channel is activated
     *
     * @return boolean
     */
    function isActive() {
        return $this->getData('isActivated');
    }


    /**
     * Accessor for the type attribute
     *
     * @return string
     */
    public function getType() {
        return $this->getData('channelType');
    }


    /**
     * Return the label for the posting channel
     *
     * @return string
     */
    function getTypeLabel() {
        $posterType = $this->getData('channelType');
        $socialMediaPlugin =& PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);
        $label = $socialMediaPlugin->getSocialMediaPlatformPluginForPostingChannelType($posterType)->name;

        return $label;
    }


    /**
     * Add the message queue for the channel
     */
    function getMessageQueue(){
        if ($this->_messageQueue == null) {
            $this->_messageQueue = new MessageQueue($this->getId(), $this->contextId);
        }

        return $this->_messageQueue;
    }


    /**
     * Add the message poster
     */
    function getMessagePoster(){
        if ($this->_messagePoster == null) {
            $posterType = $this->getData('channelType');
            $socialMediaPlugin =& PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);
            $this->_messagePoster = $socialMediaPlugin->getMessagePosterByType($posterType);
        }

        return $this->_messagePoster;

        // Allow the message poster to get the needed settings
        $this->messagePoster->addSettings($this);
    }


    /**
     * Schedule messages for posting
     *
     * @param $messages
     */
    public function scheduleMessages($messages) {
        if (!is_array($messages)) {
            $messages = [$messages];
        }

        $this->getMessageQueue()->push($messages, $this->contextId);
    }


    /**
     * Post scheduled messages
     *
     * @return array message log
     */
    function postMessages() {
        if(!$this->isActive()) {
            $this->messageLog[] = "Not posting because channel is not active.";
            return;
        }

        $frequencyAmount = $this->getData("frequencyAmount");
        $frequencyUnit = $this->getData("frequencyUnit");
        $messageQueue = $this->getMessageQueue();

        if ($messageQueue->hasMessageToPost($frequencyAmount, $frequencyUnit)) {
            $messageToPost = $messageQueue->nextMessage();

            $messagePoster = $this->getMessagePoster();

            $messagePoster->addSettings($this);

            if ($messagePoster == null) {
                $this->messageLog[] = "No message poster found that could post the message";
                return $this->messageLog;
            }

            $result = $messagePoster->postMessage($messageToPost);

            if ($result['state'] == 'success') {
                $messageQueue->popNextMessage();
                $this->messageLog[] = 'Message posted successfully';
            } else {
                $this->messageLog[] = sprintf("Message #%d could not be posted.", $messageToPost->getId());
                $this->messageLog[] = sprintf("Error: %s", $result['errorMessage']);
            }
        } else {
            $this->messageLog[] = "Nothing to post";
        }

        return $this->messageLog;
    }
}

?>
