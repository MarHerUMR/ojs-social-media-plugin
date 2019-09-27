<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/form/AddPostingChannelForm.inc.php
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class AddPostingChannelForm
 * @ingroup controllers_grid_postingChannels
 *
 * @brief Form for manager to add posting channels.
 */
import('lib.pkp.classes.form.Form');

class AddPostingChannelForm extends Form {
    /** @var int */
    var $_contextId;

    /**
     * Constructor
     *
     * @param $contextId int
     */
    function __construct($contextId) {
        $this->_contextId = $contextId;

        $this->socialMediaPlugin = PluginRegistry::getPlugin(
            'generic',
            SOCIAL_MEDIA_PLUGIN_NAME
        );

        $template = $this->socialMediaPlugin->getTemplatePath() . join(DIRECTORY_SEPARATOR, [
            "controllers",
            "grid",
            "form",
            "addPostingChannelForm.tpl",
        ]);

        parent::__construct($template);

        // Add form checks
        // Name is provided
        $this->addCheck(new FormValidatorLocale(
            $this,
            'postingChannelName',
            'required',
            'manager.announcements.form.titleRequired'
        ));

        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }


    /**
     * Get the current context id.
     * @return int
     */
    function getContextId() {
        return $this->_contextId;
    }


    /**
     * @copydoc Form::fetch()
     */
    function fetch($request) {
        $templateMgr = TemplateManager::getManager($request);
        $postingChannelTypeOptions = $this->socialMediaPlugin->getPostingChannelTypeNames();
        $templateMgr->assign('postingChannelTypes', $postingChannelTypeOptions);

        return parent::fetch($request);
    }

    /**
     * Assign form data to user-submitted data.
     */
    function readInputData() {
        $this->readUserVars(['postingChannelName', 'postingChannelType']);
    }

    /**
     * Save posting channel
     *
     * @param $request PKPRequest
     *
     * @return int
     */
    function execute($request) {
        import('plugins.generic.socialMedia.classes.autoposter.PostingChannelDAO');

        $postingChannelDao = new PostingChannelDAO();
        $postingChannel = $postingChannelDao->newDataObject();

        $contextId = $request->getContext()->getId();
        $postingChannel->setContextId($contextId);

        // Add posting channel settings
        $postingChannel->setData(
            'channelName',
            $this->getData('postingChannelName')[$this->getFormLocale()]
        );

        $socialMediaPlugin = PluginRegistry::getPlugin(
            'generic',
            SOCIAL_MEDIA_PLUGIN_NAME
        );

        $postingChannelTypes = $socialMediaPlugin->getPostingChannelTypes();
        $postingChannelType = $postingChannelTypes[$this->getData('postingChannelType')];

        $postingChannel->setData('channelType', $postingChannelType);

        $postingChannelDao->insertObject($postingChannel);

        return $postingChannel->getId();
    }
}

?>
