<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/MessageQueueGridHandler.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class MessageQueueGridHandler
 * @ingroup controllers_grid_postingChannels
 *
 * @brief Handle message queue grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('plugins.generic.socialMedia.controllers.grid.MessageQueueGridCellProvider');
import('plugins.generic.socialMedia.classes.autoposter.SocialMediaMessagesDAO');

class MessageQueueGridHandler extends GridHandler {
    //
    // Overridden template methods
    //
    /**
     * @copydoc GridHandler::authorize()
     */
    function authorize($request, &$args, $roleAssignments) {
        import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
        $this->addPolicy(new ContextRequiredPolicy($request));

        $returner = parent::authorize($request, $args, $roleAssignments);

        return $returner;
    }


    /**
     * @copydoc GridHandler::initialize()
     */
    function initialize($request, &$args = null) {
        parent::initialize($request, $args);

        $this->plugin = PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);

        $cellProvider = new MessageQueueGridCellProvider();
        $cellTemplate = $this->plugin->getTemplatePath() . '/controllers/grid/gridCell.tpl';

        $this->AddColumn(
            new GridColumn(
                'message',
                'plugins.generic.socialMedia.form.autoposter.message',
                null,
                $cellTemplate,
                $cellProvider
            )
        );
    }


    /**
     * @copydoc Gridhandler::fetchGrid()
     */
    function fetchQueueGrid($args, $request) {
        $this->_isFetchForArchive = false;
        $this->setTitle('plugins.generic.socialMedia.form.autoposter.messageQueue');
        // Set the no items row text
        $this->setEmptyRowText('plugins.generic.socialMedia.form.autoposter.noMessagesInQueue');
        return $this->fetchGrid($args, $request);
    }


    /**
     * @copydoc Gridhandler::fetchGrid()
     */
    function fetchArchiveGrid($args, $request) {
        $this->_isFetchForArchive = true;
        $this->setTitle('plugins.generic.socialMedia.form.autoposter.messageQueueArchive');
        // Set the no items row text
        $this->setEmptyRowText('plugins.generic.socialMedia.form.autoposter.noMessagesInArchive');
        return $this->fetchGrid($args, $request);
    }


    function updateMessage($args, $request) {
        $messageId = $args['messageId'];

        $datePosted = new DateTime();
        $datePosted->setTimestamp(intval($args['datePosted']));

        $socialMediaMessagesDao = new SocialMediaMessagesDAO();

        $message = $socialMediaMessagesDao->getByMessageId($messageId);

        $contextId = $request->getContext()->getId();

        if ($message->getData('contextId') == $contextId) {
            // Make sure you only alter messages that belong to the request context
            $message->setDatePosted($datePosted->format("Y-m-d H:i:s"));
            $socialMediaMessagesDao->updateMessage($message);
        }

        // 0000-00-00 00:00:00
    }


    /**
     * Get the path for the grid templates
     */
    function getTemplatePath() {
        $subpath = join(DIRECTORY_SEPARATOR, ['controllers', 'grid']);
        return $this->plugin->getTemplatePath() . $subpath . DIRECTORY_SEPARATOR;
    }


    /**
     * @copydoc GridHandler::loadData()
     */
    protected function loadData($request, $filter) {
        import('plugins.generic.socialMedia.classes.autoposter.PostingChannelDAO');
        $context = $request->getContext();
        $postingChannelId = (int)$request->getUserVar('postingChannelId');

        $postingChannelDao = new PostingChannelDAO();
        $rangeInfo = $this->getGridRangeInfo($request, $this->getId());

        $postingChannel = $postingChannelDao->getById(
            $context->getId(),
            $request->getUserVar('postingChannelId')
        );

        $messageQueue = $postingChannel->getMessageQueue();

        if ($this->_isFetchForArchive){
            return $messageQueue->getPostedMessages();
        } else {
            return $messageQueue->getUnpostedMessages();
        }
    }
}

?>
