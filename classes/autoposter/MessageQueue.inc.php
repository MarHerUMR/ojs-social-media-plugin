<?php
/**
 * @file plugins/generic/socialMedia/classes/autoposter/MessageQueue.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class MessageQueue
 * @ingroup plugins_generic_socialMedia
 *
 * @brief A message queue for a posting channel
 */

import('plugins.generic.socialMedia.classes.autoposter.SocialMediaMessagesDAO');

class MessageQueue {
    /**
     * Constructor
     *
     * @param $channelId int
     * @param $contextId int
     */
    function __construct($channelId, $contextId) {
        $this->channelId = $channelId;
        $this->contextId = $contextId;
        $this->_messages = null;
        $this->messagesDAO = new SocialMediaMessagesDAO();
    }


    /**
     * Return the sum of the messages in the queue
     *
     * @return int
     */
    function length() {
        return count($this->_messages);
    }


    /**
     * Load the messages from the database
     */
    private function loadMessages() {
        $this->_messages = $this->messagesDAO->getByChannelId(
            $this->channelId,
            $this->contextId
        )->toArray();
    }


    /**
     * Return an array of unposted messages
     *
     * @return array
     */
    function getUnpostedMessages() {
        if ($this->_messages == null) {
            $this->loadMessages();
        }

        $unpostedMessages = array();

        foreach ($this->_messages as $message) {
            if (!$message->wasPosted) {
                array_push($unpostedMessages, $message);
            }
        }

        return $unpostedMessages;
    }


    /**
     * Return an array of posted messages
     *
     * @return array
     */
    function getPostedMessages() {
        if ($this->_messages == null) {
            $this->loadMessages();
        }

        $postedMessages = array();

        foreach ($this->_messages as $message) {
            if ($message->wasPosted) {
                array_push($postedMessages, $message);
            }
        }

        return $postedMessages;
    }


    /**
     * Get the count of messages that have not been posted yet
     *
     * @return int
     */
    function getUnpostedMessageCount() {
        return count($this->getUnpostedMessages());
    }


    /**
     * Return the next message in the queue
     *
     * @return SocialMediaMessage
     */
    public function nextMessage() {
        foreach ($this->_messages as $message) {
            if (!$message->wasPosted) {
                return $message;
            }
        }

        return null;
    }


    /**
     * Get last posted message
     *
     * @return SocialMediaMessage
     */
    function getLastPostedMessage() {
        $postedMessages = array_filter(
            $this->_messages,
            function ($e) {
                return $e->wasPosted;
            }
        );

        return array_pop($postedMessages);
    }


    /**
     * Return wheter there's a message due to post
     *
     * @param $frequencyAmount int
     * @param $frequencyUnit string
     *
     * @return boolean
     */
    function hasMessageToPost($frequencyAmount, $frequencyUnit) {
        $this->loadMessages();

        if ($this->_messages == null) return false;

        $nextMessage = $this->nextMessage();

        // There is no message to post left
        if ($nextMessage == null) return false;

        // There is a message to post, but it is not due
        if (!$this->isMessageDue($frequencyAmount, $frequencyUnit)) return false;

        return true;
    }


    /**
     * Add message(s) at the end of the queue
     *
     * @param $messages string
     * @param $contextId int
     *
     */
    public function push($messages, $contextId) {
        import('plugins.generic.socialMedia.classes.autoposter.SocialMediaMessage');
        $this->loadMessages();

        foreach ($messages as $messageText) {
            $message = new SocialMediaMessage();
            $message->setValue($messageText);
            $message->setChannelId($this->channelId);
            $message->setContextId($contextId);
            $message->setDatePosted("0000-00-00 00:00:00");

            if ($this->messagesDAO->insertObject($message)){
                array_push($this->_messages, $message);
            } else {
                $errMsg = sprintf("Failed to insert \"%s\" into DB", $messageText);
                error_log($errMsg);
            }
        }
    }


    /**
     * Remove the next message from the queue
     *
     * This should be called after the 'next message' has been posted successfully
     */
    public function popNextMessage() {
        $message = $this->nextMessage();
        $now = new DateTime();
        $message->setDatePosted($now->format("Y-m-d H:i:s"));

        $this->messagesDAO->updateMessage($message);
    }


    /**
     * Check if a new message should be posted
     *
     * @param $frequencyAmount int
     * @param $frequencyUnit string
     *
     * @return boolean
     */
    private function isMessageDue($frequencyAmount, $frequencyUnit) {
        $lastPostedMessage = $this->getLastPostedMessage();

        // If there was no message posted yet then it's due
        if ($lastPostedMessage == null) return true;

        $lastPostDate = new DateTime($lastPostedMessage->getDatePosted());

        $interval = new DateInterval(sprintf(
            "P%s%s%s",
            ($frequencyUnit == "D") ? "" : "T",
            $frequencyAmount,
            $frequencyUnit
        ));

        $dueDate = $lastPostDate->add($interval);

        if(new DateTime() >= $dueDate) {
            return true;
        }

        return false;
    }
}

?>
