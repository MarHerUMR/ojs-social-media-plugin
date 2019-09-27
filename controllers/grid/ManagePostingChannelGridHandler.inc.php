<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/form/ManagePostingChannelGridHandler.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class ManagePostingChannelGridHandler
 * @ingroup controllers_grid_postingChannels
 *
 * @brief Handle posting channel management grid requests.
 */

import('plugins.generic.socialMedia.controllers.grid.PostingChannelGridHandler');
import('plugins.generic.socialMedia.controllers.grid.form.AddPostingChannelForm');
import('plugins.generic.socialMedia.controllers.grid.form.EditPostingChannelForm');
import('plugins.generic.socialMedia.controllers.grid.form.MessageQueueForm');
import('plugins.generic.socialMedia.controllers.grid.form.PersonalPlatformSettingsForm');

class ManagePostingChannelGridHandler extends PostingChannelGridHandler {
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();

        $this->addRoleAssignment(
            ROLE_ID_MANAGER,
            [
                'addPostingChannel',
                'addPostingChannelAction',
                'deletePostingChannel',
                'editPostingChannel',
                'fetchGrid',
                'fetchRow',
                'personalPlatformSettings',
                'showMsgArchive',
                'showMsgQueue',
                'showMsgQueueView',
                'updatePostingChannel',
                'updatePersonalPlatformSettings',
            ]
        );
    }


    //
    // Overridden template methods
    //
    /**
     * @copydoc AnnouncementGridHandler::initialize()
     */
    function initialize($request) {
        parent::initialize($request);

        $this->setTitle('plugins.generic.socialMedia.form.autoposter.postingChannel');

        // Load language components
        AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);

        // Add grid action.
        $router = $request->getRouter();

        import('lib.pkp.classes.linkAction.request.AjaxModal');

        $this->addAction(
            new LinkAction(
                'plugins.generic.socialMedia.form.autoposter.personalPlatformSettings',
                new AjaxModal(
                    $router->url($request, null, null, 'personalPlatformSettings', null, null),
                    __('plugins.generic.socialMedia.form.autoposter.personalPlatformSettings'),
                    'modal_add_item',
                    true
                ),
                __('plugins.generic.socialMedia.form.autoposter.personalPlatformSettings'),
                'add_item',
                __('plugins.generic.socialMedia.form.autoposter.personalPlatformSettings')
            )
        );

        $this->addAction(
            new LinkAction(
                'plugins.generic.socialMedia.form.autoposter.addPostingChannel',
                new AjaxModal(
                    $router->url($request, null, null, 'addPostingChannel', null, null),
                    __('plugins.generic.socialMedia.form.autoposter.addPostingChannel'),
                    'modal_add_item',
                    true
                ),
                __('plugins.generic.socialMedia.form.autoposter.addPostingChannel'),
                'add_item',
                __('plugins.generic.socialMedia.form.autoposter.addPostingChannel')
            )
        );
    }


    /**
     * @copydoc GridHandler::initFeatures()
     */
    function initFeatures($request, $args) {
        import('lib.pkp.classes.controllers.grid.feature.PagingFeature');
        return array(new PagingFeature());
    }


    /**
     * @copydoc GridHandler::getRowInstance()
     */
    protected function getRowInstance() {
        import('plugins.generic.socialMedia.controllers.grid.PostingChannelGridRow');
        return new PostingChannelGridRow();
    }


    /**
     * @copydoc GridHandler::authorize()
     */
    function authorize($request, &$args, $roleAssignments) {
        import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
        $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments, false);
    }


    //
    // Public grid actions
    //
    /**
     * Display form to add posting channel
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return string
     */
    function addPostingChannel($args, $request) {
        $context = $request->getContext();

        $addPostingChannelForm = new addPostingChannelForm($context->getId());
        $addPostingChannelForm->initData($args, $request);

        return new JSONMessage(true, $addPostingChannelForm->fetch($request));
    }


    /**
     * Save inserted posting channel
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    function addPostingChannelAction($args, $request) {
        $context = $request->getContext();

        $addPostingChannelForm = new addPostingChannelForm($context->getId());
        $addPostingChannelForm->readInputData();

        if ($addPostingChannelForm->validate()) {
            $postingChannelId = $addPostingChannelForm->execute($request);

            $notificationManager = new NotificationManager();
            $user = $request->getUser();
            $notificationManager->createTrivialNotification(
                $user->getId(),
                NOTIFICATION_TYPE_SUCCESS,
                ['contents' => __('plugins.generic.socialMedia.form.autoposter.postingChannelAdded')]
            );

            // Prepare the grid row data.
            return DAO::getDataChangedEvent($postingChannelId);
        } else {
            return new JSONMessage(false);
        }
    }


    /**
     * Display form to edit an posting channel.
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return string
     */
    function editPostingChannel($args, $request) {
        $postingChannelId = (int)$request->getUserVar('postingChannelId');
        $context = $request->getContext();
        $contextId = $context->getId();

        $postingChannelForm = new EditPostingChannelForm($contextId, $postingChannelId);
        $postingChannelForm->initData($args, $request);

        return new JSONMessage(true, $postingChannelForm->fetch($request));
    }


    /**
     * Display the form to edit the personal platform settings
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return string
     */
    function personalPlatformSettings($args, $request) {
        $settingsForm = new PersonalPlatformSettingsForm($request);
        $settingsForm->initData();

        return new JSONMessage(true, $settingsForm->fetch($request));
    }


    /**
     * Update the personal platform settings
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    function updatePersonalPlatformSettings($args, $request) {
        $settingsForm = new PersonalPlatformSettingsForm($request);

        $settingsForm->readInputData();

        $notificationManager = new NotificationManager();
        $user = $request->getUser();

        $settingsForm->execute();

        $notificationManager->createTrivialNotification(
            $user->getId(),
            NOTIFICATION_TYPE_SUCCESS,
            ['contents' => __('plugins.generic.socialMedia.form.autoposter.personalPlatformSettingsEditSuccess')]
        );

        return DAO::getDataChangedEvent($user->getId());
    }

    /**
     * Display a view of a posting channel message queue
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return string
     */
    function showMsgQueueView($args, $request) {
        $contextId = $request->getContext()->getId();
        $postingChannelId = (int)$request->getUserVar('postingChannelId');

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign("postingChannelId", $postingChannelId);

        $SMPlugin = PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);

        return new JSONMessage(
            true,
            $templateMgr->fetch($SMPlugin->getTemplatePath() . "messageQueue.tpl")
        );
    }


    /**
     * Display a posting channel message queue
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return string
     */
    function showMsgQueue($args, $request) {
        return $this->getMsgQueueViewContent($args, $request);
    }


    /**
     * Display a posting channel message archive
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return string
     */
    function showMsgArchive($args, $request) {
        return $this->getMsgQueueViewContent($args, $request);
    }


    /**
     * Helper method for the content of the message queue content view
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return string
     */
    function getMsgQueueViewContent($args, $request) {
        $contextId = $request->getContext()->getId();
        $postingChannelId = (int)$request->getUserVar('postingChannelId');

        $messageQueueForm = new MessageQueueForm(
            $contextId,
            $postingChannelId,
            ($request->getRequestedOp() == 'showMsgArchive') ? true : false
        );

        return new JSONMessage(true, $messageQueueForm->fetch($request));
    }

    /**
     * Update posting channel settings
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    function updatePostingChannel($args, $request) {
        $postingChannelId = (int) $request->getUserVar('postingChannelId');
        $context = $request->getContext();
        $contextId = $context->getId();

        $postingChannelForm = new EditPostingChannelForm($contextId, $postingChannelId);
        $postingChannelForm->readInputData();

        if ($postingChannelForm->validate()) {
            $notificationManager = new NotificationManager();
            $user = $request->getUser();

            $postingChannelForm->execute();

            $notificationManager->createTrivialNotification(
                $user->getId(),
                NOTIFICATION_TYPE_SUCCESS,
                ['contents' => __('plugins.generic.socialMedia.form.autoposter.postingChannelEditSuccess')]
            );

            return DAO::getDataChangedEvent($postingChannelId);
        } else {
            return new JSONMessage(false);
        }
    }


    /**
     * Delete posting channel
     *
     * @param $args arras
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    function deletePostingChannel($args, $request) {
        import('plugins.generic.socialMedia.classes.autoposter.PostingChannelDAO');

        $postingChannelId = (int) $request->getUserVar('postingChannelId');
        $contextId = $request->getContext()->getId();

        $postingChannelDao = new PostingChannelDAO();
        $postingChannel = $postingChannelDao->getById($contextId, $postingChannelId);

        if ($postingChannel && $request->checkCSRF()) {
            $postingChannelDao->deleteObject($postingChannel);

            // Create notification.
            $notificationManager = new NotificationManager();
            $user = $request->getUser();

            $notificationManager->createTrivialNotification(
                $user->getId(),
                NOTIFICATION_TYPE_SUCCESS,
                ['contents' => __('plugins.generic.socialMedia.form.autoposter.postingChannelDeleted')]
            );

            return DAO::getDataChangedEvent($postingChannelId);
        }
    }
}

?>
