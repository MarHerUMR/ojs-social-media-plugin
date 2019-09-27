<?php

/**
 * @file plugins/generic/socialMedia/AutoPosterTask.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class AutoPosterTask
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Implemting the core social media functionality
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');

class AutoPosterTask extends ScheduledTask {
    /**
     * Constructor.
     * @param $args array
     */
    function __construct($args) {
        PluginRegistry::loadCategory('generic');
        $plugin = PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);
        $this->_plugin = $plugin;

        parent::__construct($args);
    }


    /**
     * @copydoc ScheduledTask::executeActions()
     */
    protected function executeActions() {
        $executionLogMessages = $this->_plugin->loadAutoposter();

        return $executionLogMessages;
    }


    function getName() {
        return __('plugins.generic.socialMedia.autoposter.taskName');
    }
}

?>
