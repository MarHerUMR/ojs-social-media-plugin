<?php
/**
 * @file plugins/generic/socialMedia/controllers/JsInternationalizationHandler.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class JsInternationalizationHandler
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Return the translated strings for JS components
 */

import('lib.pkp.classes.handler.PKPHandler');

class JsInternationalizationHandler extends PKPHandler {
    /**
     * Constructor
     */
    function __construct(){
        parent::__construct();
    }


    /**
     * Return the requested messages
     *
     * @param $args
     * @param $request Request
     *
     * @return string
     */
    function index($args, $request) {
        $labels = [
            "messageQueue.connectionSuccess" => __("plugins.generic.socialMedia.form.autoposter.fb.messageQueue.connectionSuccess"),
            "messageQueue.notAuthorized" => __("plugins.generic.socialMedia.form.autoposter.fb.messageQueue.notAuthorized"),
            "messageQueue.authorizationExpired" => __("plugins.generic.socialMedia.form.autoposter.fb.messageQueue.authorizationExpired"),
            "messageQueue.insufficientRights" => __("plugins.generic.socialMedia.form.autoposter.fb.messageQueue.insufficientRights"),
            "messageQueue.notLoggedIn" => __("plugins.generic.socialMedia.form.autoposter.fb.messageQueue.notLoggedIn"),
            "messageQueue.noLongerLoggedIn" => __("plugins.generic.socialMedia.form.autoposter.fb.messageQueue.noLongerLoggedIn"),
            "messageQueue.scheduleMessageError" => __("plugins.generic.socialMedia.form.autoposter.fb.messageQueue.scheduleMessageError"),
            "channelSettings.connectionSuccess" => __("plugins.generic.socialMedia.form.autoposter.fb.channelSettings.connectionSuccess"),
            "channelSettings.appIdMissing" => __("plugins.generic.socialMedia.form.autoposter.fb.channelSettings.appIdMissing"),
            "channelSettings.notAuthorized" => __("plugins.generic.socialMedia.form.autoposter.fb.channelSettings.notAuthorized"),
            "channelSettings.authorizationExpired" => __("plugins.generic.socialMedia.form.autoposter.fb.channelSettings.authorizationExpired"),
            "channelSettings.insufficientRights" => __("plugins.generic.socialMedia.form.autoposter.fb.channelSettings.insufficientRights"),
            "channelSettings.notLoggedInYet" => __("plugins.generic.socialMedia.form.autoposter.fb.channelSettings.notLoggedInYet"),
            "channelSettings.noLongerLoggedIn" => __("plugins.generic.socialMedia.form.autoposter.fb.channelSettings.noLongerLoggedIn"),
            "channelSettings.noPageToChooseFrom" => __("plugins.generic.socialMedia.form.autoposter.fb.channelSettings.noPageToChooseFrom")
        ];

        return json_encode($labels);
    }

}

?>
