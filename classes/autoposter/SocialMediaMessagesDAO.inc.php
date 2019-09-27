<?php
/**
 * @file plugins/generic/socialMedia/classes/autoposter/SocialMediaMessagesDAO.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class MessageQueueDAO
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Operations for retrieving and modifying social media messages.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.socialMedia.classes.autoposter.SocialMediaMessage');

class SocialMediaMessagesDAO extends DAO {
    /**
     * Constructor.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Internal function to return a MessageQueue object from a row.
     *
     * @param $row array
     *
     * @return MessageQueue
     */
    function _fromRow($row) {
        $message = $this->newDataObject();
        $message->setId($row['message_id']);
        $message->setContextId($row['context_id']);
        $message->setChannelId($row['channel_id']);
        $message->setValue($row['value']);
        $message->setDateAdded($row['date_added']);
        $message->setDatePosted($row['date_posted']);

        return $message;
    }

    /**
     * Instantiate a new data object.
     *
     * @return MessageQueue
     */
    function newDataObject() {
        return new SocialMediaMessage();
    }

    /**
     * Get a set of social media messages by channel id
     *
     * @param $channelId int
     * @param $contextId int
     * @param $rangeInfo Object optional
     *
     * @return DAOResultFactory
     */
    function getByChannelId($channelId, $contextId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM social_media_messages
             WHERE channel_id = ?
             AND context_id = ?
             ORDER BY date_posted ASC,
             message_id ASC',
            [
                (int) $channelId,
                (int) $contextId
            ],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_fromRow');
    }


    /**
     * Get a message by it the message id
     *
     * @param $messageId int
     *
     * @return SocialMediaMessage
     */
    function getByMessageId($messageId) {
        $result = $this->retrieve(
            'SELECT * FROM social_media_messages
            WHERE message_id = ?',
            (int) $messageId
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        return $returner;
    }


    /**
     * Get the ID of the last inserted messsage.
     *
     * @return int Inserted message ID
     */
    function getInsertId() {
        return $this->_getInsertId('social_media_messages', 'message_id');
    }


    /**
     * Insert a new message
     *
     * @param $message
     *
     * @return int Inserted message id
     */
    function insertObject($message) {
        $this->update(
            'INSERT INTO social_media_messages
                (context_id, channel_id, value, date_added, date_posted)
            VALUES
                (?, ?, ?, NOW(), ?)',
            [
                (int) $message->getContextId(),
                (int) $message->getChannelId(),
                $message->getValue(),
                $message->getDatePosted()
            ]
        );

        $message->setId($this->getInsertId());
        return $message->getId();
    }


    /**
     * Update a message
     *
     * @param $message
     *
     * @return boolean
     */
    function updateMessage($message) {
        $returner = $this->update(
            sprintf('UPDATE social_media_messages
                SET
                    context_id = ?,
                    channel_id = ?,
                    value = ?,
                    date_posted = ?
                WHERE message_id = %s',
                $message->getId()),
            [
                (int) $message->getContextId(),
                (int) $message->getChannelId(),
                (string) $message->getValue(),
                (string) $message->getDatePosted(),
            ]
        );

        return $returner;
    }
}

?>
