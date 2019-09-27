<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/form/EditPostingChannelForm.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class EditPostingChannelForm
 * @ingroup controllers_grid_postingChannels
 *
 * @brief Form for manager to edit posting channels.
 */

import('lib.pkp.classes.form.Form');
import('plugins.generic.socialMedia.classes.autoposter.PostingChannelDAO');

class EditPostingChannelForm extends Form {
    /** @var int */
    var $_contextId;

    /** @var int */
    var $_postingChannelId;

    /** @var string */
    var $_channelType;

    /**
     * Constructor
     *
     * @param $contextId int
     * @param $postingChannelId int
     */
    function __construct($contextId, $postingChannelId) {
        $this->_contextId = $contextId;
        $this->setPostingChannelId($postingChannelId);

        $this->socialMediaPlugin = PluginRegistry::getPlugin(
            'generic',
            SOCIAL_MEDIA_PLUGIN_NAME
        );

        $template = $this->socialMediaPlugin->getTemplatePath() . join(DIRECTORY_SEPARATOR, [
            "controllers",
            "grid",
            "form",
            "editPostingChannelForm.tpl"
        ]);

        // Check if the platform plugin has a custom form template
        $customTemplate = $this->getCustomTemplate();

        if ($customTemplate != "") {
            $template = $customTemplate;
        }

        $this->additionalSettings = [];

        parent::__construct($template);

        // Add form checks
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));

        // Name is set
        $this->addCheck(new FormValidator(
            $this, 'postingChannelName', 'required',
            'plugins.generic.socialMedia.form.autoposter.postingChannelNameRequired'
        ));

        // Frequency is valid
        $this->addCheck(new FormValidatorRegExp(
            $this,
            'postingChannelFrequency',
            'required',
            'plugins.generic.socialMedia.form.autoposter.postingChannelFrequencyInvalid',
            '/(\d+[D|H|M])/'
        ));

        // Add the validators of the additional settings
        $this->additionalSettings = array_merge($this->additionalSettings, $this->getAdditionalSettings());

        foreach ($this->additionalSettings as $setting) {
            if ($setting->validator) {
                $this->addCheck($setting->validator);
            }
        }
    }


    /**
     * Initialize form data from current posting channel
     */
    function initData() {
        if (isset($this->_postingChannelId)) {
            $postingChannelDao = new PostingChannelDAO();
            $postingChannel = $postingChannelDao->getById(
                $this->_contextId,
                $this->getPostingChannelId()
            );

            if ($postingChannel != null) {
                // Add the common settings
                $this->_data = [
                    'postingChannelName' => $postingChannel->getData('channelName'),
                    'postingChannelTypeLabel' => $postingChannel->getTypeLabel(),
                    'postingChannelActive' => (bool)$postingChannel->getData('isActivated'),
                    'frequency' => $postingChannel->getFrequencyString(),
                ];

                $this->setChannelType($postingChannel->getData('channelType'));

                // Add the channel type specific settings
                foreach ($this->additionalSettings as $setting) {
                    $this->setData($setting->id, $postingChannel->getData($setting->id));
                }
            }
        }
    }


    /**
     * Assign form data to user-submitted data.
     */
    function readInputData() {
        $vars = [
            'postingChannelId',
            'postingChannelActive',
            'postingChannelName',
            'postingChannelType',
            'postingChannelFrequency',
        ];

        foreach ($this->getAdditionalSettings() as $setting) {
            array_push($vars, $setting->id);
        }

        $this->readUserVars($vars);
    }


    /**
     * @copydoc Form::fetch()
     */
    function fetch($request) {
        $templateManager = TemplateManager::getManager($request);

        $router = $request->getRouter();
        $currentUser = $request->getUser();

         $templateManager->assign('facebookCookieConsent', $currentUser->getData('facebookCookieConsent'));

        $templateManager->assign('postingChannelId', $this->getPostingChannelId());
        $templateManager->assign('postingChannelName', $this->getData('postingChannelName'));
        $templateManager->assign('postingChannelTypeLabel', [$this->getData('postingChannelTypeLabel')]);
        $templateManager->assign('postingChannelFrequency', $this->getData('frequency'));
        $templateManager->assign('postingChannelActive', $this->getData('postingChannelActive'));

        $templateManager->assign(
            'i18nLoaderURL',
            $this->socialMediaPlugin->getJSFolderURL() . DIRECTORY_SEPARATOR . "i18nLoader.js"
        );

        $templateManager->assign(
            'viewControllerURL',
            $this->socialMediaPlugin->getJSFolderURL() . DIRECTORY_SEPARATOR . "FBEditPostingChannelViewController.js"
        );

        // Set the values of the additional settings objects
        foreach ($this->additionalSettings as $setting) {
            $setting->value = $this->getData($setting->id);
        }

        $templateManager->assign('additionalSettings', $this->additionalSettings);

        $additionalContent = $this->getAdditionalContent();
        if ($additionalContent) {
            $templateManager->assign('additionalContent', $additionalContent);
        }

        // Add the url for the link to the platform settings
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


    /**
     * Save posting channel settings
     */
    function execute() {
        $postingChannelDao = new PostingChannelDAO();

        // Get posting channel pre update
        $postingChannel = $postingChannelDao->getById($this->_contextId, $this->getPostingChannelId());

        // Update properties
        $postingChannel->setFrequency($this->getData('postingChannelFrequency'));
        $postingChannel->setData('channelName', $this->getData('postingChannelName'));
        $postingChannel->setData('isActivated', $this->getData('postingChannelActive'));

        // Update properties from additional settings
        foreach ($this->additionalSettings as $setting) {
            $postingChannel->setData($setting->id, $this->getData($setting->id));
        }

        HookRegistry::call('editpostingchannelform::execute', array_merge([$this], func_get_args(), [&$returner] ));

        // Write to database
        $postingChannelDao->updateSettings($postingChannel, $this->additionalSettings);
    }


    /**
     * Get the type of the posting channel
     *
     * @return string
     */
    public function getChannelType() {
        if($this->_channelType == null) {
            $postingChannelDao = new PostingChannelDAO();
            $postingChannelType = $postingChannelDao->getTypeById(
                $this->_contextId,
                $this->_postingChannelId
            );

            $this->setChannelType($postingChannelType);
        }

        return $this->_channelType;
    }


    /**
     * Get the id of the posting channel
     *
     * @return int
     */
    public function getPostingChannelId() {
        return $this->_postingChannelId;
    }


    //
    // Private helper methods
    //
    /**
     * Return additional settings a platform plugin may require for a
     * posting channel
     *
     * @return array
     */
    private function getAdditionalSettings() {
        return $this->getPlatformPlugin()->getAdditionalPostingChannelSettings($this);
    }


    /**
     * Return additional content a platform plugin may display on the settings form
     *
     * @return array
     */
    private function getAdditionalContent() {
        return $this->getPlatformPlugin()->getAdditionalPostingChannelContent($this);
    }


    /**
     * Return the overriding form template
     *
     * @return string
     */
    private function getCustomTemplate() {
        return $this->getPlatformPlugin()->getCustomFormTemplate();
    }


    /**
     * Return the social media platform plugin matching the channel type
     *
     * @return SocialMediaPlatformPlugin
     */
    private function getPlatformPlugin() {
        $channelType = $this->getChannelType();

        $platformPlugin = $this->socialMediaPlugin->getSocialMediaPlatformPluginForPostingChannelType($channelType);
        return $platformPlugin;
    }


    /**
     * Set the posting channel id
     *
     * @param $id int
     */
    private function setPostingChannelId($id) {
        $this->_postingChannelId = $id;
    }


    /**
     * Set the type of the channel
     *
     * @param $type string
     */
    private function setChannelType($type) {
        $this->_channelType = $type;
    }
}

?>
