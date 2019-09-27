<?php
/**
 * @file plugins/generic/socialMedia/SocialMediaPlugin.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class SocialMediaPlugin
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Implemting the core social media functionality
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class SocialMediaPlugin extends GenericPlugin {
    /**
     * Constructor
     */
    function __construct() {
        if (!defined('SOCIAL_MEDIA_PLUGIN_NAME')) {
            define('SOCIAL_MEDIA_PLUGIN_NAME', $this->getName());
        }

        $this->request = Application::getRequest();
    }


    /**
     * Register the plugin, attaching to hooks as necessary.
     *
     * @param $category string
     * @param $path string
     *
     * @return boolean
     */
    function register($category, $path, $mainContextId = null) {
        if (parent::register($category, $path)) {

            if ($this->getEnabled()) {
                // Register the social media DAO.
                $socialMediaDao = $this->getSocialMediaDAO();
                DAORegistry::registerDAO('SocialMediaDAO', $socialMediaDao);

                // Settings
                HookRegistry::register('Templates::Management::Settings::website', [$this, 'showWebsiteSettingsTabsCallback']);

                HookRegistry::register('userdao::getAdditionalFieldNames', [$this, 'userGetAdditionalFieldNamesCallback']);

                // Register the components this plugin implements to
                // permit administration of social media settings.
                HookRegistry::register('LoadComponentHandler', [$this, 'loadComponentHandlerCallback']);

                HookRegistry::register('LoadHandler', [$this, 'loadHandlerCallback']);

                // Meta tags
                HookRegistry::register('TemplateManager::display', [$this, 'templateManagerDisplayCallback'], HOOK_SEQUENCE_LATE);

                // Add LESS / CSS
                HookRegistry::register('PageHandler::getCompiledLess', [$this, 'getCompiledLessCallback']);

                // Register acron task
                HookRegistry::register('AcronPlugin::parseCronTab', [$this, 'parseCronTabCallback']);

                // Schedule messages
                HookRegistry::register('IssueGridHandler::publishIssue', [$this, 'publishIssueCallback']);

                // Sidebar blocks
                $this->import('SocialMediaBlockPlugin');
                $blockPlugin = new SocialMediaBlockPlugin($this);
                PluginRegistry::register('blocks', $blockPlugin, $this->getPluginPath());
            }

            return true;
        }

        return false;
    }

    //
    // Callbacks for hooks
    //
    /**
     * Callback to insert additional contents to the page like meta tags
     *
     * @param $hookName string The name of the invoked hook
     * @param $args array Hook parameters
     *
     * @return boolean Hook handling status
     */
    function templateManagerDisplayCallback($hookName, $args) {
        $this->showSocialMediaMetaTags($args);

        // Add the styles
        $templateManager =& $args[0];
        $templateManager->addStyleSheet(
            'social-media-sidebar',
            $this->getURLForStyleName('social-media-sidebar'),
            ['contexts' => ['frontend']]
        );

        $templateManager->addStyleSheet(
            'social-media-form',
            $this->getURLForStyleName('social-media-form'),
            ['contexts' => ['backend']]
        );

        if ($args[1] == "frontend/pages/privacy.tpl") {
            $statement = $templateManager->get_template_vars('privacyStatement');

            $templateManager->assign('privacyStatement', $statement . $this->getPrivacyPolicyExtension());
        }

        // Permit other plugins to continue interacting with this hook
        return false;
    }


    /**
     * Add the compiled css used by this plugin
     *
     * @param $hookName string The name of the invoked hook
     * @param $args array Hook parameters
     *
     * @return boolean Hook handling status
     */
    function getCompiledLessCallback($hookName, $args) {
        $request = $args['request'];
        $name = $args['name'];

        // Ignore all styles not applying to this plugin
        if (strpos($name, 'social-media') === false) return;

        switch ($name) {
            case 'social-media-sidebar':
                $templateManager = TemplateManager::getManager($this->request);

                $args['styles'] = $templateManager->compileLess(
                    'sidebar-block',
                    $this->getStyleFolderPath() . 'sidebar-block.less'
                );
                break;

            case 'social-media-form':
                $templateManager = TemplateManager::getManager($this->request);

                $args['styles'] = $templateManager->compileLess(
                    'form',
                    $this->getStyleFolderPath() . 'form.less'
                );
                break;

            case 'social-media-sharing-buttons':
                $templateManager = TemplateManager::getManager($this->request);

                $args['styles'] = $templateManager->compileLess(
                    'sidebar-block',
                    $this->getStyleFolderPath() . 'sharing-buttons.less'
                );
                break;

            default:
                break;
        }

        // Permit other plugins to continue interacting with this hook
        return false;
    }


    /**
     * @copydoc AcronPlugin::parseCronTab()
     */
    function parseCronTabCallback($hookName, $args) {
        $taskFilesPath =& $args[0];
        $taskFilesPath[] = $this->getPluginPath() . DIRECTORY_SEPARATOR . 'scheduledTasks.xml';

        // Permit other plugins to continue interacting with this hook
        return false;
    }


    /**
     * Extend the website settings tabs to include social media
     *
     * @param $hookName string The name of the invoked hook
     * @param $args array Hook parameters
     *
     * @return boolean Hook handling status
     */
    function showWebsiteSettingsTabsCallback($hookName, $args) {
        $output =& $args[2];
        $request =& Registry::get('request');
        $dispatcher = $request->getDispatcher();

        // Add a new tab for social media
        $url = $dispatcher->url(
            $request,
            ROUTE_COMPONENT,
            null,
            'plugins.generic.socialMedia.controllers.grid.SocialMediaGridHandler',
            'index'
        );
        $output .= '<li><a name="socialMedia" href="' . $url . '">' . __('plugins.generic.socialMedia.displayName') . '</a></li>';

        // Permit other plugins to continue interacting with this hook
        return false;
    }


    /**
     * Handle page loads
     *
     * @param $hookName string The name of the invoked hook
     * @param $args array Hook parameters
     *
     * @return boolean Hook handling status
     */
    public function loadHandlerCallback($hookName, $args) {
        $request = $this->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        $page =& $args[0];
        $op =& $args[1];

        if ($page == 'privacyPolicy' && $op == 'socialMediaPlugin') {
            define('HANDLER_CLASS', 'PrivacyPolicyHandler');
            $this->import('PrivacyPolicyHandler');

            PrivacyPolicyHandler::setPlugin($this);

            return true;
        }

        // Twitter OAuth page
        if ($page == 'socialMedia' && $op == 'twitterOauthCallback') {
            define('HANDLER_CLASS', 'TwitterOauthCallbackHandler');
            $this->import('plugins.twitter.TwitterOauthCallbackHandler');

            TwitterOauthCallbackHandler::setPlugin($this);

            return true;
        }

        return false;
    }


    /**
     * Permit requests to the social media grid handler
     *
     * @param $hookName string The name of the hook being invoked
     * @param $args array The parameters to the invoked hook
     *
     * @return boolean Hook handling status
     */
    function loadComponentHandlerCallback($hookName, $args) {
        $component =& $args[0];

        switch ($component) {
            case 'plugins.generic.socialMedia.controllers.grid.SocialMediaGridHandler':
                import('plugins.generic.socialMedia.controllers.grid.SocialMediaGridHandler');
                return true;
                break;

            case 'plugins.generic.socialMedia.controllers.grid.ManagePostingChannelGridHandler':
                import('plugins.generic.socialMedia.controllers.grid.ManagePostingChannelGridHandler');
                return true;
                break;

            case 'plugins.generic.socialMedia.controllers.grid.MessageQueueGridHandler':
                import('plugins.generic.socialMedia.controllers.grid.MessageQueueGridHandler');
                return true;
                break;

            case 'plugins.generic.socialMedia.plugins.facebook.controllers.grid.FacebookMessageQueueGridHandler':
                import('plugins.generic.socialMedia.plugins.facebook.controllers.grid.FacebookMessageQueueGridHandler');
                return true;
                break;

            case 'plugins.generic.socialMedia.controllers.JsInternationalizationHandler':
                import('plugins.generic.socialMedia.controllers.JsInternationalizationHandler');
                return true;
                break;

            default:
                // Permit other plugins to continue interacting with this hook
                return false;
                break;
        }

        // Permit other plugins to continue interacting with this hook
        return false;
    }


    /**
     * Schedule messages on issue publication
     *
     * @param $hookName string The name of the hook being invoked
     * @param $args array The parameters to the invoked hook
     *
     * @return boolean Hook handling status
     */
    function publishIssueCallback($hookName, $args) {
        // Fetch active posting channels
        import('plugins.generic.socialMedia.classes.autoposter.PostingChannelDAO');
        $postingChannelDao = new PostingChannelDAO();
        $contextId = $this->request->getContext()->getId();
        $activeChannels = $postingChannelDao->getActivePostingChannelsByContextId($contextId);

        $issue = $args[0];

        // Iterate channels and add messages
        foreach ($activeChannels as $channel) {
            $channelType = $channel->getType();
            $platformPlugin = $this->getSocialMediaPlatformPluginForPostingChannelType($channelType);

            $messages = $platformPlugin->getMessagesForPublishedIssue($issue);

            $channel->scheduleMessages($messages);
        }

        // Permit other plugins to continue interacting with this hook
        return false;
    }


    /**
     * Extend the userDAO for additional settings
     *
     * @param $hookName string The name of the hook being invoked
     * @param $args array The parameters to the invoked hook
     *
     * @return boolean Hook handling status
     */
    function userGetAdditionalFieldNamesCallback($hookName, $args) {
        // TODO: Extend for other plugins
        $names =& $args[1];
        $names[] = "facebookCookieConsent";

        return false;
    }

    //
    // Meta tag methods
    //

    /**
     * Add the social media meta tags to the head
     *
     * @param $hookName string The name of the hook being invoked
     * @param $args array The parameters to the invoked hook
     *
     * @return boolean Hook handling status
     */
    function showSocialMediaMetaTags($args) {
        $contextId = $this->getContextId();

        $socialMediaDAO = $this->getSocialMediaDAO();
        $templateManager =& $args[0];

        $settings = $socialMediaDAO->getSettingsByContextId($contextId);

        if ($settings->getSettingByName('enableSocialMediaTags')) {
            if ($this->socialMediaTagsSupportedForPage()) {
                $tags = [];

                // Get all social media platform plugins and get the tags for each plugin
                foreach ($this->getSocialMediaPlatformPlugins() as $platformPlugin) {
                    $pluginTags = $platformPlugin->getSocialMediaMetaTags();

                    if ($pluginTags != null) {
                        $tags = array_merge($tags, $pluginTags);
                    }
                }

                // Headers have to have an unique name so each tag get's a number appended
                $tagIdNr = 0;

                foreach ($tags as $tag) {
                    $templateManager->addHeader(
                        'socialMediaTag' . $tagIdNr,
                        $tag['content']
                    );

                    $tagIdNr++;
                }
            }
        }

        return false;
    }


    /**
     * Check if the requested page and op should have social media tags added
     *
     * @return boolean
     */
    function socialMediaTagsSupportedForPage() {
        $request = $this->getRequest();
        $requestedPage = $request->_router->getRequestedPage($request);
        $requestedOp = $request->_router->getRequestedOp($request);

        $pageAndOpKey = "$requestedPage:$requestedOp";

        $supportedPages = [
            ":",
            "about:aboutThisPublishingSystem",
            "about:contact",
            "about:editorialTeam",
            "about:index",
            "about:submissions",
            "announcement:index",
            "announcement:view",
            "article:view",
            "index:",
            ":index",
            "index:index",
            "indexJournal:",
            "information:authors",
            "information:competingInterestGuidelines",
            "information:librarians",
            "information:readers",
            "information:sampleCopyrightWording",
            "issue:archive",
            "issue:view",
            "login:index",
            "login:lostPassword",
            "search:authors",
            "search:index",
            "search:search",
            "user:register",
        ];

        if (array_search($pageAndOpKey, $supportedPages) !== false) {
            return true;
        }

        return false;
    }


    /**
     * Add meta tags to page
     *
     * @param $templateManager The template manager
     * @param $tag Social media meta tag
     */
    function addMetaTag($templateManager, $tag) {
        $templateManager = "";

        $templateManager->addHeader(
            $tag->name,
            $tag->content(),
            ['contexts' => ['frontend']]
        );
    }


    /**
     * Get the activated social media platform plugins
     *
     * @return array
     */
    function getSocialMediaPlatformPlugins() {
        if (!isset($this->_platformPlugins)) {
            $platformPlugins = [];

            import('plugins.generic.socialMedia.plugins.twitter.TwitterPlugin');
            import('plugins.generic.socialMedia.plugins.facebook.FacebookPlugin');

            // Facebook
            $facebookPlugin = new FacebookPlugin([
                "pluginPath" => join(
                    DIRECTORY_SEPARATOR,
                    [$this->getPluginPath(), "plugins", "facebook", "" ]
                )
            ]);

            array_push($platformPlugins, $facebookPlugin);

            // Twitter
            $twitterPlugin = new TwitterPlugin([
                "pluginPath" => join(
                    DIRECTORY_SEPARATOR,
                    [$this->getPluginPath(), "plugins", "twitter", ""]
                )
            ]);

            array_push($platformPlugins, $twitterPlugin);


            HookRegistry::call('SocialMedia::socialMediaPlatformPlugins', [&$platformPlugins]);

            $this->_platformPlugins = $platformPlugins;
        }

        return $this->_platformPlugins;
    }


    /**
     * Get the social media platform plugin of by name
     *
     * @param $name String
     *
     * @return SocialMediaPlatformPlugin
     */
    public function getSocialMediaPlatformPluginByName($name) {
        $platformPlugins = $this->getSocialMediaPlatformPlugins();

        foreach ($platformPlugins as $plugin) {
            if ($plugin->name == $name) {
                return $plugin;
            }
        }

        return null;
    }


    //
    // Autoposting
    //

    /**
     * Get the possible posting channel types
     *
     * @return array
     */
    function getPostingChannelTypes() {
        $types = [];

        foreach ($this->getSocialMediaPlatformPlugins() as $platformPlugin) {
            if ($platformPlugin->supportsAutoposting()) {
                array_push($types, $platformPlugin->getPostingChannelType());
            }
        }

        sort($types, SORT_NATURAL | SORT_FLAG_CASE);

        return $types;
    }


    /**
     * Get the possible posting channel type names
     *
     * @return array
     */
    function getPostingChannelTypeNames() {
        $types = [];

        foreach ($this->getSocialMediaPlatformPlugins() as $platformPlugin) {
            if ($platformPlugin->supportsAutoposting()) {
                array_push($types, $platformPlugin->getPostingChannelTypeName());
            }
        }

        sort($types, SORT_NATURAL | SORT_FLAG_CASE);

        return $types;
    }


    /**
     * Return a platform plugin for channel type
     *
     * @param $type Posting channel type
     */
    function getSocialMediaPlatformPluginForPostingChannelType($type) {
        foreach ($this->getSocialMediaPlatformPlugins() as $platformPlugin) {
            if ($platformPlugin->supportsAutoposting()) {
                if ($platformPlugin->getPostingChannelType() == $type) {
                    return $platformPlugin;
                }
            }
        }

        error_log(sprintf("No platform plugin found for channel type: %s", $type));

        return null;
    }


    /**
     * Return a message poster for a given type.
     *
     * @param $type
     *
     * @return MessagePoster
     */
    function getMessagePosterByType($type) {
        $messagePosters = [];

        $platformPlugin = $this->getSocialMediaPlatformPluginForPostingChannelType($type);

        if ($platformPlugin != null) {
            $poster = $platformPlugin->getMessagePoster();
            return $poster;
        }

        error_log(sprintf("No message poster found for type: %s", $type));

        return null;
    }


    /**
     * Load the autoposters and execute posting if there are messages to post
     *
     * @return array The array
     */
    function loadAutoposter() {
        $messageLog = [];

        import('plugins.generic.socialMedia.classes.autoposter.Autoposter');

        $autoposter = new Autoposter();
        $executionLog = $autoposter->execute();

        array_merge($messageLog, $executionLog);

        return $messageLog;
    }


    /**
     * Return the posting channel with the id
     *
     * @param $channelId
     *
     * @return PostingChannel
     */
    function getPostingChannelById($channelId) {
        $channel = null;

        $postingChannelDao = $this->getPostingChannelDAO();

        $channel = $postingChannelDao->getById($this->getContextId(), $channelId);

        return $channel;
    }


    //
    // Helper functions
    //

    /**
     * Helper method to get the context id
     *
     * @return int context id
     */
    function getContextId() {
        $request = $this->getRequest();
        $router = $request->getRouter();
        $contextId = $router->getContext($request)->getData('id');

        return $contextId;
    }


    /**
     * Return a PostingChannelDAO
     *
     * @return PostingChannelDAO
     */
    public function getPostingChannelDAO() {
        import('plugins.generic.socialMedia.classes.autoposter.PostingChannelDAO');

        return new PostingChannelDAO();
    }


    /**
     * Return a SocialMediaDAO
     *
     * @return SocialMediaDAO
     */
    public function getSocialMediaDAO() {
        import('plugins.generic.socialMedia.classes.SocialMediaDAO');

        return new SocialMediaDAO();
    }


    /**
     * Get the filename of the ADODB schema for this plugin.
     *
     * @return string Full path and filename to schema descriptor.
     */
    function getInstallSchemaFile() {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'schema.xml';
    }


    /**
     * @copydoc PKPPlugin::getTemplatePath
     */
    function getTemplatePath($inCore = false) {
        return parent::getTemplatePath($inCore) . 'templates' . DIRECTORY_SEPARATOR;
    }


    /**
     * @copydoc PKPPlugin::getTemplatePath
     */
    function getPluginsPath($inCore = false) {
        return parent::getTemplatePath($inCore) . 'plugins' . DIRECTORY_SEPARATOR;
    }


    /**
     * Get the path of the styles folder
     *
     * @return string
     */
    function getStyleFolderPath() {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR;
    }


    /**
     * Get the url for the JS folder
     *
     * @return string
     */
    public function getJSFolderURL() {
        $request = $this->getRequest();
        return join(
            DIRECTORY_SEPARATOR,
            [
                $request->getBaseUrl(),
                "plugins",
                "generic",
                "socialMedia",
                "js"
            ]
        );
    }


    /**
     * Get the full url for a given style name
     *
     * @param string style name
     *
     * @return string full url
     */
    function getURLForStyleName($styleName) {
        $request = $this->getRequest();
        $dispatcher = $this->request->getDispatcher();

        return $dispatcher->url(
            $request,
            ROUTE_COMPONENT,
            null,
            'page.PageHandler',
            'css',
            null,
            ['name' => $styleName]
        );
    }


    /**
     * @copydoc Plugin::getName()
     */
    function getName() {
        return 'SocialMediaPlugin';
    }


    /**
     * Get the plugin's display (human-readable) name.
     *
     * @return string
     */
    function getDisplayName() {
        return __('plugins.generic.socialMedia.displayName');
    }


    /**
     * Get the plugin's display (human-readable) description.
     *
     * @return string
     */
    function getDescription() {
        return __('plugins.generic.socialMedia.description');
    }


    /**
     * Determine whether or not this plugin has a privacy policy
     *
     * @return boolean
     */
    function getHasPrivacyPolicy() {
        return true;
    }
}

?>
