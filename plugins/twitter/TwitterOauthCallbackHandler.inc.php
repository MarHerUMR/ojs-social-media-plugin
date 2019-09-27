<?php
/**
 * @file plugins/generic/socialMedia/plugins/twitter/TwitterOauthCallbackHandler.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class TwitterOauthCallbackHandler
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Handle requests for the Twitter oauth callbacks.
 */

import('classes.handler.Handler');
import('plugins.generic.socialMedia.plugins.twitter.classes.TwitterRequestTokenRequest');
import('plugins.generic.socialMedia.plugins.twitter.classes.TwitterAccessTokenRequest');
import('plugins.generic.socialMedia.plugins.twitter.classes.TwitterConsumer');

class TwitterOauthCallbackHandler extends Handler {
    /** @var SocialMediaPlugin The social media plugin */
    static $plugin;
    private $queryArgs;

    /**
     * Constructor
     */
    public function __construct() {
        $this->addRoleAssignment(
            [ROLE_ID_MANAGER],
            ['twitterOauthCallback']
        );
    }


    /**
     * @copydoc PKPHandler::authorize()
     */
    function authorize($request, &$args, $roleAssignments) {
        import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
        $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments);
    }


    /**
     * Handle socialMediaPlugin request
     *
     * @param $args array Arguments array.
     * @param $request PKPRequest Request object.
     */
    public function twitterOauthCallback($args, $request) {
        $this->request = $request;

        if (empty($args)) {
            // Id is missing. Show error message
            $this->displayChannelIdMissing();
            return;
        }

        import('plugins.generic.socialMedia.classes.autoposter.PostingChannelDAO');

        $postingChannelId = $args[0];
        $contextId = $request->getContext()->getId();

        $postingChannelDao = new PostingChannelDAO();
        $this->postingChannel = $postingChannelDao->getById($contextId, $postingChannelId);

        if (!$this->postingChannel) {
            $this->displayPostingChannelMissing();
            return;
        }

        if ($this->postingChannel->getType() != "twitter") {
            $this->displayPostingChannelWrongType();
            return;
        }

        if (!$this->channelHasConsumerKeyAndSecret()) {
            $this->displayPostingChannelMissingConsumer();
            return;
        }

        $this->queryArgs = $this->request->getQueryArray();

        if (empty($this->queryArgs)) {
            $this->makeRequestTokenRequest();
            return;
        }

        if ($this->isDeniedCallback()) {
            $this->displayDenied();
            return;
        }

        if ($this->isRequestForAccessToken()) {
            $this->makeAccessTokenRequest();
            return;
        }

        if ($this->isAccessTokenRequestCallback()) {
            $this->displayAccessToken();
            return;
        }
    }


    /**
     * Display the page with the access token and access token secret
     */
    private function displayAccessToken($token, $tokenSecret, $username) {
        $templateMgr = TemplateManager::getManager($this->request);
        $twitterPlugin = self::$plugin->getSocialMediaPlatformPluginByName("Twitter");

        $templateMgr->assign(
            "pageTitle",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.accessCredentialsTitle")
        );

        $templateMgr->assign("accessToken", $token);
        $templateMgr->assign("accessTokenSecret", $tokenSecret);
        $templateMgr->assign("username", $username);

        $templateMgr->display(
            $twitterPlugin->getTemplatePath() . DIRECTORY_SEPARATOR . "twitterOauthSuccess.tpl"
        );
    }


    /**
     * Display the error page when the posting channel id is missing
     */
    private function displayChannelIdMissing() {
        $templateMgr = TemplateManager::getManager($this->request);

        $templateMgr->assign(
            "pageTitle",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.channelIdMissingTitle")
        );

        $templateMgr->assign(
            "heading",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.channelIdMissingTitle")
        );

        $templateMgr->assign(
            "paragraph",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.channelIdMissingParagraph")
        );

        $templateMgr->display(
            $this->getTwitterPlugin()->getTemplatePath() . DIRECTORY_SEPARATOR . "twitterOauthError.tpl"
        );
    }


    /**
     * Display the error page when the posting channel has the wrong type
     */
    private function displayPostingChannelWrongType() {
        $templateMgr = TemplateManager::getManager($this->request);

        $templateMgr->assign(
            "pageTitle",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.wrongTypeTitle")
        );

        $templateMgr->assign(
            "heading",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.wrongTypeTitle")
        );

        $templateMgr->assign(
            "paragraph",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.wrongTypeParagraph")
        );

        $templateMgr->display(
            $this->getTwitterPlugin()->getTemplatePath() . DIRECTORY_SEPARATOR . "twitterOauthError.tpl"
        );
    }


    /**
     * Display the error page when user has denied access
     */
    private function displayDenied() {
        $templateMgr = TemplateManager::getManager($this->request);

        $templateMgr->assign(
            "pageTitle",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.deniedTitle")
        );

        $templateMgr->assign(
            "heading",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.deniedTitle")
        );

        $templateMgr->assign(
            "paragraph",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.deniedParagraph")
        );

        $templateMgr->display(
            $this->getTwitterPlugin()->getTemplatePath() . DIRECTORY_SEPARATOR . "twitterOauthError.tpl"
        );
    }


    /**
     * Display the error page when user has denied access
     */
    private function displayPostingChannelMissingConsumer() {
        $templateMgr = TemplateManager::getManager($this->request);

        $templateMgr->assign(
            "pageTitle",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.missingConsumerTitle")
        );

        $templateMgr->assign(
            "heading",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.missingConsumerTitle")
        );

        $templateMgr->assign(
            "paragraph",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.missingConsumerParagraph")
        );

        $templateMgr->display(
            $this->getTwitterPlugin()->getTemplatePath() . DIRECTORY_SEPARATOR . "twitterOauthError.tpl"
        );
    }


    /**
     * Display the error page when no posting channel could be found
     */
    private function displayPostingChannelMissing() {
        $templateMgr = TemplateManager::getManager($this->request);

        $templateMgr->assign(
            "pageTitle",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.channelNotFoundTitle")
        );

        $templateMgr->assign(
            "heading",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.channelNotFoundTitle")
        );

        $templateMgr->assign(
            "paragraph",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.channelNotFoundParagraph")
        );

        $templateMgr->display(
            $this->getTwitterPlugin()->getTemplatePath() . DIRECTORY_SEPARATOR . "twitterOauthError.tpl"
        );
    }


    /**
     * Display the error page for a general error while making a request to Twitter.
     */
    private function displayRequestError($error = null) {
        $templateMgr = TemplateManager::getManager($this->request);

        $templateMgr->assign(
            "pageTitle",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.requestErrorTitle")
        );

        $templateMgr->assign(
            "heading",
            __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.requestErrorTitle")
        );

        if ($error == null) {
            $templateMgr->assign(
                "paragraph",
                __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.requestDefaultErrorParagraph")
            );
        } else {
            $errorParagraph = __("plugins.generic.socialMedia.autoposter.twitter.callbackPage.requestDefaultErrorParagraph");
            $errorParagraph .= sprintf("<br><br>Error code %s:<br>%s", $error['code'], $error['message']);

            $templateMgr->assign("paragraph", $errorParagraph);
        }

        $templateMgr->display(
            $this->getTwitterPlugin()->getTemplatePath() . DIRECTORY_SEPARATOR . "twitterOauthError.tpl"
        );
    }


    /**
     * Make the request to Twitter to get the request token.
     */
    private function makeRequestTokenRequest() {
        $key = $this->postingChannel->getData("consumerKey");
        $secret = $this->postingChannel->getData("consumerSecret");

        $consumer = new TwitterConsumer($key, $secret);
        $twitterRequest = new TwitterRequestTokenRequest(
            $consumer,
            ['oauth_callback' => $this->getCallbackUrl()]
        );

        $result = $twitterRequest->makeRequest();

        if($result == "error") {
            // Handle unknown error
            $this->displayRequestError();
        } else if (is_array($result) && array_key_exists("code", $result) && array_key_exists("message", $result)) {
            // Handle Twitter api error
            $this->displayRequestError($result);
        } else if ($result) {
            // Handle an valid result
            $url = $twitterRequest->getRedirectURL() . $result;
            $this->request->redirectUrl($url);
        }
    }


    /**
     * Make the request to Twitter to get the access token and token secret.
     *
     */
    private function makeAccessTokenRequest() {
        $key = $this->postingChannel->getData("consumerKey");
        $secret = $this->postingChannel->getData("consumerSecret");

        $consumer = new TwitterConsumer($key, $secret);

        $twitterRequest = new TwitterAccessTokenRequest($consumer, [
            "oauth_token" => $this->queryArgs['oauth_token'],
            "oauth_verifier" => $this->queryArgs['oauth_verifier'],
            "oauth_callback" => $this->getCallbackUrl()
        ]);

        $result = $twitterRequest->makeRequest();

        if ($result) {
            $params = [];
            parse_str($result, $params);
            $this->displayAccessToken($params['oauth_token'], $params['oauth_token_secret'], $params['screen_name']);
        }
    }


    /**
     * Check if a request for an access token should be made
     *
     * @return boolean
     */
    private function isRequestForAccessToken() {
        if (!array_key_exists("oauth_token", $this->queryArgs)) return false;
        if (!array_key_exists("oauth_verifier", $this->queryArgs)) return false;

        return true;
    }


    /**
     * Check if the request is the callback of the request for an access token
     *
     * @return boolean
     */
    private function isAccessTokenRequestCallback() {
        if (!array_key_exists("oauth_token", $this->queryArgs)) return false;
        if (!array_key_exists("oauth_token_secret", $this->queryArgs)) return false;

        return true;
    }


    /**
     * Check if the request is the callback of the request for an access token but the user has denied access
     *
     * @return boolean
     */
    private function isDeniedCallback() {
        if (array_key_exists("denied", $this->queryArgs)) return true;

        return false;
    }


    /**
     * Chech wheter the posting channel has a consumer key and consumer secret, bot needed for the requests to twitter.
     *
     * @return boolean
     */
    private function channelHasConsumerKeyAndSecret() {
        if ($this->postingChannel->getData("consumerKey") == "") return false;
        if ($this->postingChannel->getData("consumerSecret") == "") return false;

        return true;
    }


    private function getCallbackUrl() {
        return $this->getTwitterPlugin()->getCallbackUrl($this->postingChannel->getId());
    }


    /**
     * Provide the social media plugin to the handler.
     * @param $plugin SocialMediaPlugin
     */
    static function setPlugin($plugin) {
        self::$plugin = $plugin;
    }


    /**
     * Return the twitter plugin
     *
     * @return TwitterPlugin
     */
    private function getTwitterPlugin() {
        return self::$plugin->getSocialMediaPlatformPluginByName("Twitter");
    }
}

?>