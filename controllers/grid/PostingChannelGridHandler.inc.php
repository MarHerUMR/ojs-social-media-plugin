<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/PostingChannelGridHandler.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class PostingChannelGridHandler
 * @ingroup controllers_grid_postingChannels
 *
 * @brief Handle posting channel grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('plugins.generic.socialMedia.controllers.grid.form.SocialMediaSettingsForm');
import('plugins.generic.socialMedia.controllers.grid.PostingChannelGridCellProvider');

class PostingChannelGridHandler extends GridHandler {
    public function __construct($dataProvider = null) {
        $this->socialMediaPlugin = PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);

        parent::__construct($dataProvider);
    }

    //
    // Overridden template methods
    //
    /**
     * @copydoc GridHandler::authorize()
     * @param $requireAutopostingEnabled Iff true, allow access only if context settings enable announcements
     */
    function authorize($request, &$args, $roleAssignments, $requireAutopostingEnabled = true) {
        import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
        $this->addPolicy(new ContextRequiredPolicy($request));

        $returner = parent::authorize($request, $args, $roleAssignments);
        $context = $request->getContext();

        $socialMediaDAO = $this->socialMediaPlugin->getSocialMediaDAO();
        $settings = $socialMediaDAO->getSettingsByContextId($context->getId());

        if ($requireAutopostingEnabled && !$settings->getSettingByName('enableAutoposting')) {
            return false;
        }

        return $returner;
    }

    /**
     * @copydoc GridHandler::initialize()
     */
    function initialize($request, $args = null) {
        parent::initialize($request, $args);

        $this->setEmptyRowText('plugins.generic.socialMedia.form.autoposter.noneExist');

        $cellProvider = new PostingChannelGridCellProvider();

        $this->addColumn(
            new GridColumn(
                'channelName',
                'plugins.generic.socialMedia.form.autoposter.channelName',
                null,
                null,
                $cellProvider
            )
        );

        $this->addColumn(
            new GridColumn(
                'type',
                'common.type',
                null,
                null,
                $cellProvider
            )
        );

        $this->addColumn(
            new GridColumn(
                'msgInQueue',
                'plugins.generic.socialMedia.form.autoposter.messagesInQueue',
                null,
                null,
                $cellProvider
            )
        );

        $this->addColumn(
            new GridColumn(
                'msgQueue',
                'plugins.generic.socialMedia.form.autoposter.messageQueue',
                null,
                null,
                $cellProvider
            )
        );
    }


    /**
     * Get the path for the grid templates
     */
     function getTemplatePath() {
         return $this->socialMediaPlugin->getTemplatePath() . join(DIRECTORY_SEPARATOR, ['controllers', 'grid']) . DIRECTORY_SEPARATOR;
     }


    /**
     * @copydoc GridHandler::loadData()
     */
    protected function loadData($request, $filter) {
        import('plugins.generic.socialMedia.classes.autoposter.PostingChannelDAO');

        $context = $request->getContext();

        $postingChannelDao = new PostingChannelDAO();
        $rangeInfo = $this->getGridRangeInfo($request, $this->getId());

        return $postingChannelDao->getPostingChannelsByContextId($context->getId());
    }
}
