<?php
/**
 * @file plugins/generic/socialMedia/classes/autoposter/PostingChannelDAO.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class PostingChannelDAO
 * @ingroup postingChannel
 *
 * @brief Operations for retrieving and modifying posting channels.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.socialMedia.classes.autoposter.PostingChannel');

class PostingChannelDAO extends DAO {
    /**
     * Constructor.
     */
    function __construct() {
        parent::__construct();
        $this->additionalSettings = [];
    }

    /**
     * Internal function to return a PostingChannel object from a row.
     * @param $row array
     * @return PostingChannel
     */
    function _fromRow($row) {
        $channel = $this->newDataObject();
        $channel->setId($row['channel_id']);
        $channel->setContextId($row['context_id']);

        $this->getDataObjectSettings('social_media_posting_channel_settings', 'channel_id', $channel->getId(), $channel);

        $channel->setFrequency($channel->getData("frequency"));

        return $channel;
    }


    /**
     * Instantiate a new data object.
     * @return PostingChannel
     */
    function newDataObject() {
        return new PostingChannel();
    }


    /**
     * Get all posting channels for a context.
     *
     * @param $contextId int
     *
     * @return DAOResultFactory containing PostingChannels
     */
    public function getPostingChannelsByContextId($contextId) {
        $params[] = (int) $contextId;
        
        $pluginTypes = $this->getActivatedPlatformpluginTypes();
        $inParamArray = [];
        $inParamString = "";

        // FIXME: The is not a good solution for the problem of inserting an array for the in clause
        foreach ($pluginTypes as $type) {
            array_push($params, $type);
            array_push($inParamArray, "?");
        }

        $inParamString = join(", ", $inParamArray);

        $result = $this->retrieve(
            'SELECT ch.*
             FROM social_media_posting_channels as ch
             LEFT JOIN social_media_posting_channel_settings as settings
             ON ch.channel_id = settings.channel_id
             WHERE ch.context_id = ? AND
             settings.setting_name = \'channelType\' AND
             settings.setting_value IN (' . $inParamString . ')
             ORDER BY ch.context_id',
            $params
        );

        $queryResults = new DAOResultFactory($result, $this, '_fromRow');

        return $queryResults;
    }


    /**
     * Get all active posting channels for a context.
     *
     * @param $contextId int
     *
     * @return Array containing PostingChannels
     */
    public function getActivePostingChannelsByContextId($contextId) {
        $postingChannels = $this->getPostingChannelsByContextId($contextId)->toArray();
        $activeChannels = [];

        foreach ($postingChannels as $channel) {
            if($channel->isActive()) {
                array_push($activeChannels, $channel);
            }
        }

        return $activeChannels;
    }


    /**
     * Get a posting channel by its id
     *
     * @param $contextId int
     * @param $channelId int
     *
     * @return PostingChannel
     */
    function getById($contextId, $channelId) {
        $params = [(int) $contextId, (int) $channelId];

        $result = $this->retrieve(
            'SELECT * FROM social_media_posting_channels WHERE
            context_id = ? AND channel_id = ?',
            $params
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }


    /**
     * Get the posting channel type for the channelId
     *
     * @param contextId int
     * @param channelId int
     *
     * @return string
     */
    function getTypeById($contextId, $channelId) {
        $channel = $this->getById($contextId, $channelId);
        return $channel->getData('channelType');
    }


    /**
     * Insert a new posting channel
     *
     * @param $channel PostingChannel
     *
     * @return int
     */
    public function insertObject($channel) {
        $this->update(
            sprintf('INSERT INTO social_media_posting_channels (context_id)
                     VALUES (?)'), [$channel->contextId]
        );

        $channel->setId($this->getInsertId());

        $this->updateSettings($channel);

        return $channel->getId();
    }


    /**
     * Update the settings for the posting channel
     *
     * @param $channel PostingChannel
     * @param $additionalSettings Array
     */
    function updateSettings($channel, $additionalSettings = []) {
        // Add the ids of the additional settings so that they get
        // updated as well
        foreach ($additionalSettings as $setting) {
            array_push($this->additionalSettings, $setting->id);
        }

        $this->updateDataObjectSettings(
            'social_media_posting_channel_settings',
            $channel,
            array('channel_id' => $channel->getId())
        );
    }


    /**
     * Delete posting channel
     *
     * @param $postingChannel PostingChannel Object
     *
     * @return boolean
     */
    function deleteObject($postingChannel) {
        return $this->deleteById($postingChannel->getId());
    }


    /**
    * Delete an posting channel by posting channel ID.
    *
    * @param $postingChannelId int
    *
    * @return boolean
    */
    function deleteById($postingChannelId) {
        $this->update(
            'DELETE FROM social_media_posting_channel_settings WHERE channel_id = ?',
            (int) $postingChannelId
        );

        return $this->update(
            'DELETE FROM social_media_posting_channels WHERE channel_id = ?',
            (int) $postingChannelId
        );
    }


    /**
     * Add additional fields that do not have
     * dedicated accessors.
     *
     * @param $additionalFieldNames array
     */
    function getAdditionalFieldNames() {
        return array_merge(
            ['channelName', 'channelType', 'frequency', 'isActivated'],
            $this->additionalSettings
        );
    }


    /**
     * Get the ID of the last inserted posting channel
     *
     * @return int
     */
    function getInsertId() {
        return $this->_getInsertId('social_media_posting_channels', 'channel_id');
    }


    /**
     * Return the types of the activated platform plugins
     *
     * @return array of strings
     */
    function getActivatedPlatformpluginTypes() {
        $plugin = PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);

        return $plugin->getPostingChannelTypes();
    }
}

?>
