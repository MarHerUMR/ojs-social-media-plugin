<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/form/MessageQueueForm.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class MessageQueueForm
 * @ingroup controllers_grid_postingChannels
 *
 * @brief Form to view and edit message queues
 */

import('lib.pkp.classes.form.Form');

class MessageQueueForm extends Form {
    /** @var int */
    var $_contextId;

    /** @var int */
    var $_postingChannelId;

    /**
     * Constructor
     *
     * @param $contextId int
     * @param $channelId int
     * @param $getArchive boolean optional
     */
    function __construct($contextId, $channelId, $getArchive = false) {
        $this->contextId = $contextId;

        $this->plugin = PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);

        $this->isArchive = $getArchive;

        $postingChannelType = $this->plugin->getPostingChannelById($channelId)->getType();
        $platformPlugin = $this->plugin->getSocialMediaPlatformPluginForPostingChannelType($postingChannelType);

        $this->additionalMessageQueueFormTemplateVariables = [];

        $this->additionalMessageQueueFormTemplateVariables = $platformPlugin->getAdditionalMessageQueueFormTemplateVariables();

        $templatePath = $platformPlugin->getMessageQueueTemplate($getArchive);

        parent::__construct($templatePath);
    }


    /**
     * @see Form::fetch
     */
    function fetch($request) {
        $templateManager = TemplateManager::getManager($request);
        $router = $request->getRouter();

        $currentUser = $request->getUser();
        $templateManager->assign('facebookCookieConsent', $currentUser->getData('facebookCookieConsent'));

        // When there's additional variables needed, fetch them from the posting channel
        if (!empty($this->additionalMessageQueueFormTemplateVariables)){
            import('plugins.generic.socialMedia.classes.autoposter.PostingChannelDAO');
            $context = $request->getContext();
            $postingChannelId = (int)$request->getUserVar('postingChannelId');

            $postingChannelDao = new PostingChannelDAO();

            $postingChannel = $postingChannelDao->getById(
                $context->getId(),
                $request->getUserVar('postingChannelId')
            );

            foreach ($this->additionalMessageQueueFormTemplateVariables as $variable) {
                $pageId = $postingChannel->getData($variable);
                $templateManager->assign($variable, $pageId);
            }
        }

        $templateManager->assign(
            'postingChannelId',
            $request->getUserVar('postingChannelId')
        );

        $templateManager->assign(
            'i18nLoaderURL',
            $this->plugin->getJSFolderURL() . DIRECTORY_SEPARATOR . "i18nLoader.js"
        );

        $templateManager->assign(
            "viewControllerURL",
            $this->plugin->getJSFolderURL() . DIRECTORY_SEPARATOR . "FBMessageQueueViewController.js"
        );

        $templateManager->assign(
            'platformSettingsAction',
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

        return parent::fetch($request);
    }
}

?>
