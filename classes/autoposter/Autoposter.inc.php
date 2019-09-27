<?php
/**
 * @file plugins/generic/socialMedia/classes/autoposter/Autoposter.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class Autoposter
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Implementing the autoposting functionality
 */

class Autoposter {
    private $messageLog = [];

    /**
     * Constructor
     */
    function __construct() {
        $this->socialMediaPlugin =& PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);

        $this->postingChannelDAO = $this->socialMediaPlugin->getPostingChannelDAO();
    }

    /**
     * Get the posting channels and post messages if there are messages to be posted.
     *
     * @return array Log messages
     */
    function execute() {
        // Get the ids of all contexts
        $contexts = Application::getContextDAO()->getAll()->toArray();
        $contextIds = array_keys($contexts);

        $socialMediaDAO = $this->socialMediaPlugin->getSocialMediaDAO();
        $postingChannelDAO = $this->socialMediaPlugin->getPostingChannelDAO();

        foreach ($contextIds as $contextId) {
            $settings = $socialMediaDAO->getSettingsByContextId($contextId);

            // Skip contexts that do not have autoposting enabled
            if (!$settings->getData("enableAutoposting")) {
                continue;
            }

            $postingChannels = $postingChannelDAO->getPostingChannelsByContextId($contextId)->toArray();

            foreach ($postingChannels as $channel) {
                // Skip channels that are not activated
                if (!$channel->isActive()) {
                    continue;
                }

                error_log(sprintf("posting Channel id: %s", print_r($channel->getId(), true)));
                $messages = $channel->postMessages();
                error_log(sprintf("messages %s", print_r($messages, true)));
                $this->messageLog = array_merge($this->messageLog, $messages);
            }
        }

        return $this->messageLog;
    }
}

?>
