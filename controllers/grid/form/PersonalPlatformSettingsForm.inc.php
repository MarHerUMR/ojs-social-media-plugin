<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/form/PersonalPlatformSettingsForm.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class PersonalPlatformSettingsForm
 * @ingroup controllers_grid_postingChannels
 *
 * @brief Form to edit the users platform settings.
 */

import('lib.pkp.classes.form.Form');

class PersonalPlatformSettingsForm extends Form {
    /**
     * Constructor
     *
     * @param $request
     */
    function __construct($request) {
        $this->request = $request;

        $this->socialMediaPlugin = PluginRegistry::getPlugin(
            'generic',
            SOCIAL_MEDIA_PLUGIN_NAME
        );

        $template = $this->socialMediaPlugin->getTemplatePath() . join(DIRECTORY_SEPARATOR, [
            "controllers",
            "grid",
            "form",
            "personalPlatformSettingsForm.tpl"
        ]);

        parent::__construct($template);
    }


    /**
     * Initialize form data from current user
     */
     function initData() {
        $currentUser = $this->request->getUser();

        $this->_data = [
            'facebookCookieConsent' => $currentUser->getData('facebookCookieConsent'),
        ];
     }


    /**
     * Assign form data to user-submitted data.
     */
    function readInputData() {
        $vars = [
            'facebookCookieConsent',
        ];

        $this->readUserVars($vars);
    }


    /**
     * @copydoc From::fetch()
     */
    function fetch($request) {
        $templateManager = TemplateManager::getManager($request);
        $router = $request->getRouter($request);

        $currentUser = $request->getUser();

        $userName = $currentUser->getData('username');
        $fullName = $currentUser->getFullName();

        $templateManager->assign('userName', $userName);
        $templateManager->assign('fullName', $fullName);
        $templateManager->assign(
            'facebookCookieConsent',
            $this->getData('facebookCookieConsent')
        );

        return parent::fetch($request);
    }

    /**
     * Save personal platform settings
     */
    function execute() {
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $userDao->getById(
            $this->request->getUser()->getId()
        );

        $user->setData(
            'facebookCookieConsent',
            $this->getData('facebookCookieConsent')
        );

        $userDao->updateLocaleFields($user);
    }
}
?>
