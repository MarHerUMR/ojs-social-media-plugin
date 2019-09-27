<?php
/**
 * @file plugins/generic/socialMedia/SocialMediaBlockPlugin.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class SocialMediaBlockPlugin
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Social Media plugin, faceting block component
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class SocialMediaBlockPlugin extends BlockPlugin {
    /**
     * Constructor
     *
     * @param $partentPlugin SocialMediaPlugin
     */
    function __construct($parentPlugin) {
        $this->parentPlugin = $parentPlugin;

        parent::__construct();
    }


    //
    // Implement template methods from Plugin.
    //

    /**
     * @see Plugin::getHideManagement()
     */
    function getHideManagement() {
        return false;
    }


    /**
     * @see Plugin::getName()
     */
    function getName() {
        return 'SocialMediaBlockPlugin';
    }


    /**
     * @see Plugin::getDisplayName()
     */
    function getDisplayName() {
        return __('plugins.generic.socialMediaSidebarBlock.displayName');
    }


    /**
     * @see Plugin::getDescription()
     */
    function getDescription() {
        return __('plugins.generic.socialMediaSidebarBlock.description');
    }


    /**
     * @see Plugin::getPluginPath()
     */
    function getPluginPath() {
        return $this->parentPlugin->getPluginPath();
    }


    /**
     * @copydoc PKPPlugin::getTemplatePath
     */
    function getTemplatePath($inCore = false) {
        return $this->parentPlugin->getTemplatePath($inCore);
    }


    /**
     * @see Plugin::getSeq()
     */
    function getSeq($contextId = null) {
        $seq = parent::getSeq();

        if (!is_numeric($seq)) $seq = 0;

        return $seq;
    }


    //
    // Implement template methods from LazyLoadPlugin
    //

    /**
     * @see LazyLoadPlugin::getEnabled()
     */
    function getEnabled($contextId = null) {
        $isSocialMediaPluginEnabled = $this->parentPlugin->getEnabled();

        if (!$isSocialMediaPluginEnabled) {
            return $isSocialMediaPluginEnabled;
        }

        $contextId = $this->getCurrentContextId();

        if ($this->isSitePlugin()) {
            $contextId = 0;
        }

        return $this->getSetting($contextId, 'enabled');
    }


    //
    // Implement template methods from BlockPlugin
    //

    /**
     * @see BlockPlugin::getBlockContext()
     */
    function getBlockContext($contextId = null) {
        return BLOCK_CONTEXT_SIDEBAR;
    }


    /**
     * Get the HTML contents for this block.
     *
     * @param $templateManager object
     * @param $request PKPRequest (Optional for legacy plugins)
     *
     * @return string
     */
    function getContents($templateManager, $request = null) {
        $blockTemplateFilename = "block.tpl";
        $blockData = array();

        if ($blockTemplateFilename === null) return "";

        foreach ($this->parentPlugin->getSocialMediaPlatformPlugins() as $plugin) {
            array_push($blockData, $plugin->getBlockData());
        }

        // Leave out the whole block instead of showing just the block heading but no content
        if (!array_filter($blockData)) {
            return;
        }

        $templateManager->smartyAssign($blockData, 'blockData');

        return $templateManager->fetch($this->getTemplatePath() . $blockTemplateFilename);
    }
}

?>
