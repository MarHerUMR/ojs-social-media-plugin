<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/form/SocialMediaSettingsForm.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class SocialMediaSettingsForm
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Form to edit social media settings.
 */

import('lib.pkp.classes.form.Form');
import('plugins.generic.socialMedia.classes.SocialMediaSettings');

class SocialMediaSettingsForm extends Form {
    /** @var int Context (press / journal) ID */
    var $contextId;

    /** @var socialMediaPlugin Social media plugin */
    var $plugin;

    /**
     * Constructor
     *
     * @param $contextId int Context ID
     */
    function __construct($contextId) {
        $this->socialMediaPlugin = PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);

        parent::__construct($this->socialMediaPlugin->getTemplatePath() . 'socialMediaSettingsForm.tpl');
        $this->contextId = $contextId;

        $additionalTagSettings = [];

        HookRegistry::call(
            'SocialMedia::settings::tagSettings',
            [$this, &$additionalTagSettings]
        );

        $this->additionalTagSettings = $additionalTagSettings;

        $additionalBlockSettings = [];
        HookRegistry::call(
            'SocialMedia::settings::blockSettings',
            [$this, &$additionalBlockSettings]
        );

        $this->additionalBlockSettings = $additionalBlockSettings;

        // Add form checks
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));

        $this->addCheck(new FormValidatorLength(
            $this,
            'metaTagTwitterSite',
            'optional',
            'plugins.generic.socialMedia.metaTagTwitterSite.lengthError',
            '<=',
            15
        ));

        $this->addCheck(new FormValidatorRegExp(
            $this,
            'metaTagTwitterSite',
            'optional',
            'plugins.generic.socialMedia.metaTagTwitterSite.invalidHandle',
            '/^([a-zA-Z0-9]){1,15}$/'
        ));

        $this->addCheck(new FormValidatorLength(
            $this,
            'blockTwitterAccount',
            'optional',
            'plugins.generic.socialMedia.metaTagTwitterSite.lengthError',
            '<=',
            15
        ));

        $this->addCheck(new FormValidatorRegExp(
            $this,
            'blockTwitterAccount',
            'optional',
            'plugins.generic.socialMedia.metaTagTwitterSite.invalidHandle',
            '/^([a-zA-Z0-9]){1,15}$/'
        ));

        // Add the validators of the additional settings
        $additionalSettings = array_merge($this->additionalTagSettings, $this->additionalBlockSettings);

        foreach ($additionalSettings as $setting) {
            if ($setting->validator) {
                $this->addCheck($setting->validator);
            }
        }

        $this->addCheck(new FormValidatorURL(
            $this,
            'blockFacebookURL',
            'optional',
            'plugins.generic.socialMedia.form.blockFacebookURL.urlError'
        ));

        $this->addCheck(new FormValidatorRegExp(
            $this,
            'blockFacebookURL',
            'optional',
            'plugins.generic.socialMedia.form.blockFacebookURL.urlError',
            '/^http[s]?:\/\/www.facebook.com\/\w+/'
        ));
    }


    /**
     * Initialize form data.
     */
    function initData() {
        $templateMgr = TemplateManager::getManager();
        $socialMediaDAO = $this->socialMediaPlugin->getSocialMediaDAO();
        $settings = $socialMediaDAO->getSettingsByContextId($this->contextId);

        $enableSocialMediaTags = ($settings->getSettingByName('enableSocialMediaTags')) ? "on" : "";
        $enableAutoposting = ($settings->getSettingByName('enableAutoposting')) ? "on" : "";

        $this->setData('enableSocialMediaTags', $enableSocialMediaTags);
        $this->setData('enableAutoposting', $enableAutoposting);
        $this->setData('metaTagTwitterSite', $settings->getSettingByName('metaTagTwitterSite'));
        $this->setData('blockTwitterAccount', $settings->getSettingByName('blockTwitterAccount'));
        $this->setData('blockFacebookURL', $settings->getSettingByName('blockFacebookURL'));

        // Add the values to the settings
        foreach ($this->additionalTagSettings as &$setting) {
            $setting->value = $settings->getSettingByName($setting->id);
        }

        foreach ($this->additionalBlockSettings as &$setting) {
            $setting->value = $settings->getSettingByName($setting->id);
        }

        // Add the additional block settings to the template manager
        $this->setData('additionalTagSettings', $this->additionalTagSettings);
        $this->setData('additionalBlockSettings', $this->additionalBlockSettings);
    }


    /**
     * Assign form data to user-submitted data.
     */
    function readInputData() {
        $settingsVars = [
            'enableSocialMediaTags',
            'enableAutoposting',
            'metaTagTwitterSite',
            'blockTwitterAccount',
            'blockFacebookURL'
        ];

        // Add the variables for additional settings
        $additionalSettings = array_merge($this->additionalTagSettings, $this->additionalBlockSettings);

        foreach ($additionalSettings as $setting) {
            array_push($settingsVars, $setting->id);
        }

        $this->readUserVars($settingsVars);
    }


    /**
     * @see Form::fetch
     */
    function fetch($request) {
        return parent::fetch($request);
    }


    /**
     * Save form values into the database
     */
    function execute() {
        $socialMediaDAO = $this->socialMediaPlugin->getSocialMediaDAO();

        $socialMediaSettings = new SocialMediaSettings();

        $socialMediaSettings->updateSetting('enableSocialMediaTags', $this->getData('enableSocialMediaTags'), 'boolean');
        $socialMediaSettings->updateSetting('enableAutoposting', $this->getData('enableAutoposting'), 'boolean');
        $socialMediaSettings->updateSetting('metaTagTwitterSite', $this->getData('metaTagTwitterSite'));
        $socialMediaSettings->updateSetting('blockTwitterAccount', $this->getData('blockTwitterAccount'));
        $socialMediaSettings->updateSetting('blockFacebookURL', $this->getData('blockFacebookURL'));

        $additionalSettings = array_merge($this->additionalTagSettings, $this->additionalBlockSettings);

        foreach ($additionalSettings as $setting) {
            $socialMediaSettings->updateSetting($setting->id, $this->getData($setting->id));
            $socialMediaDAO->addAdditionalFieldNames($setting->id);
        }

        $socialMediaSettings->contextId = $this->contextId;

        $result = $socialMediaDAO->updateSettings($socialMediaSettings);

        if ($result !== false) {
            $notificationManager = new NotificationManager();
            $user = Application::getRequest()->getUser();
            $notificationManager->createTrivialNotification($user->getId());
        }
    }
}

?>
