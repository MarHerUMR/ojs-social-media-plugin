<?php
/**
 * @file plugins/generic/socialMedia/plugins/TwitterPlugin.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class TwitterPlugin
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Providing the Twitter functionality for the social media plugin.
 */

import('plugins.generic.socialMedia.classes.AutopostingSocialMediaPlatformPlugin');

class TwitterPlugin extends AutopostingSocialMediaPlatformPlugin {
    /**
     * Contstructor
     *
     * @param $args array
     */
    function __construct($args = []) {
        parent::__construct($args);

        $context = $this->request->getContext();

        if ($context == null) {
            $this->contextId = 0;
        } else {
            $this->contextId = $context->getData('id');
        }

        $this->name = "Twitter";

        $this->getSettings();

        // Add posting channel validator
        HookRegistry::register('editpostingchannelform::validate', [$this, 'validateCallback']);
    }


    /**
     * Validate the posting channel form and give an error if the channel should be acivated
     * but the access token and/or access token secrets are empty
     *
     * @param $hookName string The name of the invoked hook
     * @param $args array Hook parameters
     *
     * @return boolean Hook handling status
     */
    public function validateCallback($hookName, $args) {
        $form =& $args[0];
        $returnValue =& $args[1];

        if ($form->getData('postingChannelActive')) {
            $fieldKeys = ["accessToken", "accessTokenSecret"];

            foreach ($fieldKeys as $key) {
                if ($form->getData($key) === "") {
                    $form->addError(
                        'isActivated',
                        __('plugins.generic.socialMedia.form.autoposter.twitter.channelSettings.activationError')
                    );

                    $returnValue = false;
                }
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
        $request = $this->request;
        $tags = [];

        $cardType = "summary";
        $description = $this->getTwitterCompatibleDescription($this->getSiteDescription());

        if ($this->settings->getSettingByName('metaTagTwitterSite') != "") {
            array_push($tags, $this->getSocialMediaTag("twitter:site", "@" . $this->settings->getSettingByName('metaTagTwitterSite')));
        }

        // Try to add an image
        $cover = $this->getPageImage();

        if ($cover['url'] != "") {
            array_push($tags, $this->getSocialMediaTag("twitter:image", $cover['url']));

            if ($cover['altText'] != "") {
                array_push($tags, $this->getSocialMediaTag("twitter:image:alt", $cover['altText']));
            }

            if ($cover['width'] and $cover['height']){
                if ($cover['width'] > $cover['height'] and $cover['width'] > 300 and $cover['height'] > 157) {
                    $cardType = "summary_large_image";
                    $coverDescription = "";
                }
            }
        }

        array_push($tags, $this->getSocialMediaTag("twitter:title", $this->getPageTitle()));
        array_push($tags, $this->getSocialMediaTag("twitter:description", $description));

        // Addding card type at the end because it depends on the image size
        array_push($tags, $this->getSocialMediaTag("twitter:card", $cardType));

        return $tags;
    }


    /**
     * Get the data used of the sidebar block
     *
     * @return array
     */
    function getBlockData() {
        $twitterHandle = $this->settings->getSettingByName('blockTwitterAccount');

        if (!$twitterHandle) {
            return [];
        }

        $defaultText = __('plugins.generic.socialMedia.defaultTwitterBlockText');

        $content = str_replace("--twitterHandle--", $twitterHandle, $defaultText);

        $blockData = [
            "serviceName" => $this->name,
            "content" => $content
        ];

        return $blockData;
    }


    /**
     * Return a description compatible to the twitter cards
     *
     * @param string $description
     *
     * @return string
     */
    function getTwitterCompatibleDescription($description) {
        $description = PKPString::regexp_replace("/&quot;/", '"', $description);

        if (mb_strlen($description, 'utf8') > 130) {
            $description = mb_substr($description, 0, 127);

            if (mb_strlen(rtrim($description), 'utf8') == 127) {
                $description = mb_substr($description, 0, 126) . " ";
            }

            $description = $description . "...";
        }

        $description = PKPString::regexp_replace('/\"/', "&quot;", $description);

        return $description;
    }


    /**
     * Return the type for a posting channel for this plugin
     *
     * @return string
     */
    function getPostingChannelType() {
        return "twitter";
    }


    /**
     * Return the type name for a posting channel for this plugin
     *
     * @return string
     */
    function getPostingChannelTypeName() {
        return "Twitter";
    }


    /**
     * @copydoc AutopostingSocialMediaPlatformPlugin::getMessagePoster()
     */
    function getMessagePoster() {
        import('plugins.generic.socialMedia.plugins.twitter.classes.TwitterMessagePoster');

        return new TwitterMessagePoster();
    }


    /**
     * @copydoc AutopostingSocialMediaPlatformPlugin::getAdditionalPostingChannelSettings()
     */
    function getAdditionalPostingChannelSettings($form) {
        import('plugins.generic.socialMedia.classes.SocialMediaPlatformSetting');

        $settings = [];

        array_push($settings, new SocialMediaPlatformSetting([
            "type" => "text",
            "id" => "consumerKey",
            "label" => "plugins.generic.socialMedia.form.autoposter.consumerKey",
            "placeholder" => "",
            "maxlength" => "50",
            "required" => false,
            "validator" => new FormValidator(
                $form, 'consumerKey', 'required',
                'plugins.generic.socialMedia.form.autoposter.consumerKeyRequired'
            )
        ]));

        array_push($settings, new SocialMediaPlatformSetting([
            "type" => "text",
            "id" => "consumerSecret",
            "label" => "plugins.generic.socialMedia.form.autoposter.consumerSecret",
            "placeholder" => "",
            "maxlength" => "50",
            "required" => false,
            "validator" => new FormValidator(
                $form, 'consumerSecret', 'required',
                'plugins.generic.socialMedia.form.autoposter.consumerSecretRequired'
            )
        ]));

        array_push($settings, new SocialMediaPlatformSetting([
            "type" => "text",
            "id" => "accessToken",
            "label" => "plugins.generic.socialMedia.form.autoposter.accessToken",
            "placeholder" => "",
            "maxlength" => "50",
            "required" => false,
            "validator" => new FormValidator(
                $form, 'accessToken', 'optional',
                'plugins.generic.socialMedia.form.autoposter.accessTokenRequired'
            )
        ]));

        array_push($settings, new SocialMediaPlatformSetting([
            "type" => "text",
            "id" => "accessTokenSecret",
            "label" => "plugins.generic.socialMedia.form.autoposter.accessTokenSecret",
            "placeholder" => "",
            "maxlength" => "50",
            "required" => false,
            "validator" => new FormValidator(
                $form, 'accessTokenSecret', 'optional',
                'plugins.generic.socialMedia.form.autoposter.accessTokenSecretRequired'
            )
        ]));

        return $settings;
    }


    /**
     * @copydoc AutopostingSocialMediaPlatformPlugin::getAdditionalPostingChannelContent()
     */
    function getAdditionalPostingChannelContent($form) {
        $channelId = $form->getPostingChannelId();
        $callbackURL = $this->getCallbackUrl($channelId);

        $templateMgr = TemplateManager::getManager($this->request);

        $templateMgr->assign("callbackURL", $callbackURL);

        return $templateMgr->fetch($this->getTemplatePath() . DIRECTORY_SEPARATOR . 'postingChannelAdditionalContent.tpl');
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
            "twitter",
            "templates"
        ]);
    }


    /**
     * Return the URL for the callback page for the Twitter authorization
     *
     * @param $channelId int
     *
     * @return string
     */
    public function getCallbackUrl($channelId) {
        return $this->request->getBaseUrl() . "/socialMedia/twitterOauthCallback/" . $channelId;
    }


    /**
     * Get the path of the the meta tag template
     *
     * @return string
     */
    function getMetaTagTemplatePath() {
        return $this->getTemplatePath() . DIRECTORY_SEPARATOR . 'twitterMetaTag.tpl';
    }


    /**
     * @copydoc AutopostingSocialMediaPlatformPlugin::getIconURLForName()
     */
    function getIconURLForName($name) {
        $baseUrl = $this->request->getBaseUrl();

        return join("/", [
            $baseUrl,
            "plugins",
            "generic",
            "socialMedia",
            "plugins",
            "twitter",
            "templates",
            "icons",
            $name
        ]);
    }
}

?>
