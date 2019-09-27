<?php

/**
 * @file plugins/generic/socialMedia/PrivacyPolicyHandler.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class PrivacyPolicyHandler
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Handle requests for the privacy policy.
 */

import('classes.handler.Handler');

class PrivacyPolicyHandler extends Handler {
    /** @var SocialMediaPlugin The social media plugin */
    static $plugin;

    /**
     * Handle socialMediaPlugin request
     *
     * @param $args array Arguments array.
     * @param $request PKPRequest Request object.
     */
    function socialMediaPlugin($args, $request) {
        $locale = AppLocale::getLocale();

        AppLocale::registerLocaleFile(
            $locale,
            "plugins/generic/socialMedia/locale/$locale/privacyPolicy.xml"
        );

        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->assign(
            "pageTitle",
            join(" ", [
                self::$plugin->getDisplayName(),
                __("common.plugin"),
                __("plugins.generic.socialMedia.privacyPolicy")
            ])
        );

        $templateMgr->assign(
            "content",
            __("plugins.generic.socialMedia.privacyPolicy.content")
        );

        $templateMgr->display(
            self::$plugin->getTemplatePath() . DIRECTORY_SEPARATOR . "privacyPolicy.tpl"
        );
    }


    /**
     * Provide the social media plugin to the handler.
     * @param $plugin SocialMediaPlugin
     */
    static function setPlugin($plugin) {
        self::$plugin = $plugin;
    }
}
