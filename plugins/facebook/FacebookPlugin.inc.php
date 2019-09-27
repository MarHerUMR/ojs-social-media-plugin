<?php
/**
 * @file plugins/generic/socialMedia/plugins/FacebookPlugin.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class FacebookPlugin
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Providing the Facebook functionality for the social media plugin.
 */

import('plugins.generic.socialMedia.classes.AutopostingSocialMediaPlatformPlugin');

class FacebookPlugin extends AutopostingSocialMediaPlatformPlugin {
    function __construct($args = array()) {
        parent::__construct($args);

        $context = $this->request->getContext();

        if ($context == null) {
            $this->contextId = 0;
        } else {
            $this->contextId = $context->getData('id');
        }

        $this->name = "Facebook";

        $this->getSettings();

        HookRegistry::register('editpostingchannelform::execute', [$this, 'executeCallback']);
    }


    /**
     * Remove the fbPageId from the form if the fbAppId is empty
     *
     * @param $hookName string The name of the invoked hook
     * @param $args array Hook parameters
     *
     * @return boolean Hook handling status
     */
    public function executeCallback($hookName, $args) {
        $form =& $args[0];

        if ($form->getChannelType() == "facebook") {
            if ($form->getData('fbAppId') == '') {
                $form->setData('fbPageId', '');
            }
        }

        // Permit other plugins to continue interacting with this hook
        return false;
    }


    /**
     * Get the meta tags for the header
     *
     * @return array An array of tag objects
     */
    function getSocialMediaMetaTags() {
        $requestedPage = $this->request->_router->getRequestedPage($this->request);
        $tags = array();

        array_push($tags, $this->getSocialMediaTag("og:title", $this->getPageTitle()));

        $type = "website";

        if ($requestedPage == "article") {
            $type = "article";
        }

        array_push($tags, $this->getSocialMediaTag("og:type", $type));

        if ($type == "article") {
            $article = $this->getTemplateVar('article');

            $publishedTime = $article->getDatePublished();
            $publishedTime = str_replace(" ", "T", $publishedTime);

            array_push($tags, $this->getSocialMediaTag("og:published_time", $publishedTime));

            $authors = $article->getAuthors();

            foreach ($authors as $author) {
                array_push($tags, $this->getSocialMediaTag("og:author", $author->getFullName()));
            }

            $section = $this->getTemplateVar('section')->getLocalizedTitle();
            array_push($tags, $this->getSocialMediaTag("og:section", $section));

            $dao = DAORegistry::getDAO('SubmissionKeywordDAO');
            $keywords = $dao->getKeywords($article->getId(), array(AppLocale::getLocale()));

            foreach ($keywords as $locale => $localeKeywords) {
                foreach ($localeKeywords as $keyword) {
                    array_push($tags, $this->getSocialMediaTag("og:tag", $keyword));
                }
            }
        }

        // Try to add an image
        $cover = $this->getPageImage();

        if ($cover) {
            array_push($tags, $this->getSocialMediaTag("og:image", $cover['url']));
            array_push($tags, $this->getSocialMediaTag("og:image:user_generated", "false"));

            if ($cover['width']) {
                array_push($tags, $this->getSocialMediaTag("og:image:width", $cover['width']));
            }

            if ($cover['height']) {
                array_push($tags, $this->getSocialMediaTag("og:image:height", $cover['height']));
            }

            if ($cover['altText'] != "") {
                array_push($tags, $this->getSocialMediaTag("og:image:alt", $cover['altText']));
            }
        }

        // url
        array_push($tags, $this->getSocialMediaTag("og:url", $this->getURL()));

        // description
        array_push($tags, $this->getSocialMediaTag("og:description", $this->getOpenGraphCompatibleDescription()));

        return $tags;
    }


    /**
     * Get the data used for the sidebar block
     *
     * @return array
     */
    function getBlockData() {
        $facebookURL = $this->settings->getSettingByName('blockFacebookURL');

        if(!$facebookURL) {
            return array();
        }

        $defaultText = __('plugins.generic.socialMedia.defaultFacebookBlockText');

        $content = str_replace("--url--", $facebookURL, $defaultText);

        $blockData = array(
            "serviceName" => $this->name,
            "content" => $content
        );

        return $blockData;
    }


    /**
     * Return the url of the requested page
     *
     * @return string
     */
    function getURL() {
        $id = null;
        $requestedPage = $this->request->_router->getRequestedPage($this->request);
        $requestedOp = $this->request->_router->getRequestedOp($this->request);
        $context = $this->request->_router->getContext($this->request);

        if (($requestedPage == "index" && $requestedOp == "index") && $context != null) {
            $requestedPage = null;
            $requestedOp = null;
        }

        if ($requestedPage == "article" && $requestedOp == "view") {
            $id = $this->getTemplateVar('article')->getBestArticleId();
        }

        if ($requestedPage == "issue" && $requestedOp == "view") {
            $id = $this->getTemplateVar('issue')->getId();
        }

        if ($requestedPage == "announcement" && $requestedOp == "view") {
            $id = $this->getTemplateVar('announcement')->getId();
        }

        $url = Request::url(null, $requestedPage, $requestedOp, $id);

        // Check if a rewritten url was requested. Leave the page and op out when the request was made without them
        if (($requestedPage == null && $requestedOp == "index") || ($requestedPage == null && $requestedOp == null)) {
            $requestedUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            if (strpos($url, $requestedUrl) !== false) {
                $url = $requestedUrl;
            }
        }

        return $url;
    }


    /**
     * Return the path of the plugins template directory
     *
     * @return string
     */
    function getTemplatePath() {
        $basePath = Core::getBaseDir();

        return join(DIRECTORY_SEPARATOR, [
            "file:$basePath",
            $this->socialMediaPluginPath,
            "plugins",
            "facebook",
            "templates"
        ]);
    }


    /**
     * Get the path of the the meta tag template
     *
     * @return string
     */
    function getMetaTagTemplatePath() {
        return $this->getTemplatePath() . DIRECTORY_SEPARATOR . 'openGraphMetaTag.tpl';
    }


    /**
     * Return the despription of the requested page.
     * Open Graph protocol: og:description - A one to two sentence description of your object.
     *
     * @param $description string
     *
     * @return string
     */
    function getOpenGraphCompatibleDescription($description = null) {
        if ($description == null) {
            $description = $this->getSiteDescription();
        }

        $sentences = PKPString::regexp_split('/(?<!\.\.\.)(?<!Dr\.)(?<=[.?!]|\.\)|\.")\s+(?=[a-zA-Z"\(])/', $description);

        if (count($sentences) > 1){
            return implode(array_slice($sentences, 0, 2), " ");
        } else {
            return $sentences[0];
        }
    }


    /**
     * Return the type for a posting channel for this plugin
     *
     * @return string
     */
    function getPostingChannelType() {
        return "facebook";
    }


    /**
     * Return the type name for a posting channel for this plugin
     *
     * @return string
     */
    function getPostingChannelTypeName() {
        return "Facebook";
    }


    /**
     * @copydoc AutopostingSocialMediaPlatformPlugin::getMessagePoster()
     */
    function getMessagePoster() {
    }


    /**
     * @copydoc AutopostingSocialMediaPlatformPlugin::getAdditionalPostingChannelSettings()
     */
    function getAdditionalPostingChannelSettings($form) {
        import('plugins.generic.socialMedia.classes.SocialMediaPlatformSetting');

        $settings = [];

        array_push($settings, new SocialMediaPlatformSetting([
            "type" => "text",
            "id" => "fbAppId",
            "label" => "plugins.generic.socialMedia.form.autoposter.fbAppId",
        ]));

        array_push($settings, new SocialMediaPlatformSetting([
            "type" => "select",
            "id" => "fbPageId",
        ]));

        return $settings;
    }


    /**
     * @see AutopostingSocialMediaPlatformPlugin::getMessageQueueTemplate
     */
    function getMessageQueueTemplate($getArchive = false) {
        if ($getArchive) {
            return parent::getMessageQueueTemplate(true);
        }

        $templatesPath = $this->getTemplatePath();

        $fullPath = join(DIRECTORY_SEPARATOR, [
            $templatesPath,
            "form",
            'messageQueueForm.tpl'
        ]);

        return $fullPath;
    }

    /**
     * @copydoc AutopostingSocialMediaPlatformPlugin::getCustomFormTemplate
     */
    function getCustomFormTemplate() {
        $socialMediaPlugin = PluginRegistry::getPlugin(
            'generic',
            SOCIAL_MEDIA_PLUGIN_NAME
        );

        $pluginsPath = $socialMediaPlugin->getPluginsPath();

         return $pluginsPath . join(DIRECTORY_SEPARATOR, [
            "facebook",
            "templates",
            "form",
            "editPostingChannelForm.tpl"
        ]);
    }

    /**
     * @see AutopostingSocialMediaPlatformPlugingetAdditionalMessageQueueFormTemplateVariables
     */
    function getAdditionalMessageQueueFormTemplateVariables() {
        return ["fbPageId", "fbAppId", "frequency"];
    }


    /**
     * Helper function to assemble the url for the plugins icons
     *
     * @param $name Filename of the icon
     */
    function getIconURLForName($name) {
        $baseUrl = $this->request->getBaseUrl();

        return join("/", [
            $baseUrl,
            "plugins",
            "generic",
            "socialMedia",
            "plugins",
            "facebook",
            "templates",
            "icons",
            $name
        ]);
    }
}

?>
