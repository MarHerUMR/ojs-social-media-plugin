<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/SocialMediaGridHandler.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class SocialMediaGridHandler
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Handle SocialMediaPlugin grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');

class SocialMediaGridHandler extends GridHandler {
    /**
     * @var SocialMediaPlugin The social media plugin
     */
    static $plugin;

    /**
     * Set the social media plugin.
     *
     * @param $plugin SocialMediaPlugin
     */
    static function setPlugin($plugin) {
        self::$plugin = $plugin;
    }


    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();

        $this->addRoleAssignment(
            array(ROLE_ID_MANAGER),
            array('index', 'updateSocialMediaSettings')
        );
    }


    //
    // Overridden template methods
    //

    /**
     * @copydoc PKPHandler::authorize()
     */
    function authorize($request, &$args, $roleAssignments) {
        import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
        $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments);
    }


    /**
     * @copydoc GridHandler::initialize()
     */
    function initialize($request, $args = null) {
        parent::initialize($request, $args);
    }


    //
    // Public Grid Actions
    //

    /**
     * Display the grid's containing page.
     *
     * @param $args array
     * @param $request PKPRequest
     */
    function index($args, $request) {
        $context = $request->getContext();
        import('plugins.generic.socialMedia.controllers.grid.form.SocialMediaSettingsForm');
        $settingsForm = new SocialMediaSettingsForm($context->getId());
        $settingsForm->initData();

        return new JSONMessage(true, $settingsForm->fetch($request));
    }


    /**
     * Update the social media settings
     *
     * @param $args array
     * @param $request PKPRequest
     */
    function updateSocialMediaSettings($args, $request) {
        import('plugins.generic.socialMedia.controllers.grid.form.SocialMediaSettingsForm');

        $context = $request->getContext();

        $settingsForm = new SocialMediaSettingsForm($context->getId());

        $settingsForm->readInputData();

        if ($settingsForm->validate()) {
            // Save the results
            $settingsForm->execute();
            return DAO::getDataChangedEvent();
        } else {
            // Present any errors
            return new JSONMessage(true, $settingsForm->fetch($request));
        }
    }
}

?>
