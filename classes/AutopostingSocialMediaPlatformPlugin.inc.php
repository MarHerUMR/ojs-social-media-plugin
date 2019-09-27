<?php
/**
 * @file plugins/generic/socialMedia/classes/AutopostingSocialMediaPlatformPlugin.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class AutopostingSocialMediaPlatformPlugin
 * @ingroup plugins_generic_socialMedia
 *
 * @brief A SocialMediaPlatformPlugin thas supports autoposting.
 */

import('plugins.generic.socialMedia.classes.SocialMediaPlatformPlugin');

abstract class AutopostingSocialMediaPlatformPlugin extends SocialMediaPlatformPlugin {
    function __construct($args =[]) {
        parent::__construct($args);
    }


    /**
     * @copydoc SocialMediaPlatformPlugin::supportsAutoposting()
     */
    function supportsAutoposting() {
        return true;
    }


    /**
     * Return the type for a posting channel for this plugin
     *
     * @return string
     */
    abstract public function getPostingChannelType();


    /**
     * Return the type name for a posting channel for this plugin
     *
     * @return string
     */
    abstract public function getPostingChannelTypeName();


    /**
     * Return posting channel specific settings for the settings form
     *
     * @param $form Form object for the form validators
     *
     * @return array containing SocialMediaPlatformSetting
     */
    function getAdditionalPostingChannelSettings($form) {
        return [];
    }


    /**
     * Return posting channel specific content for the settings form
     *
     * @param $form Form object for the form validators
     *
     * @return array containing SocialMediaPlatformSetting
     */
    function getAdditionalPostingChannelContent($form) {
        return [];
    }


    /**
     * Return a message poster
     *
     * @return MessagePoster
     */
    abstract public function getMessagePoster();


    /**
     * Generate an array of messages for the auto poster queue
     *
     * @param $issue Issue
     *
     * @return array
     */
    function getMessagesForPublishedIssue($issue) {
        $messages = [];

        array_push($messages, $this->getMessageForIssue($issue));

        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articles = $publishedArticleDao->getPublishedArticlesInSections(
            $issue->getId(), true
        );

        foreach ($articles as $section) {
            foreach ($section['articles'] as $article) {
                array_push($messages, $this->getMessageForArticle($article));
            }
        }

        return $messages;
    }


    /**
     * Return a message for autoposting for an article
     *
     * @param $article
     *
     * @return string
     */
    function getMessageForArticle($article) {
        // Format: "Article out by {author}(, {author}...). {title} {link}"
        // A maximum of 3 authors get mentioned
        $maxAuthors = 3;
        $title = $article->getLocalizedTitle();

        $dispatcher = $this->request->getDispatcher();
        $articleURL = $dispatcher->url(
            $this->request,
            ROUTE_PAGE,
            null,
            'article',
            'view',
            [$article->getBestArticleId()]
        );

        $authors = $article->getAuthors();
        $lastNames = [];

        foreach ($authors as $author) {
            $lastName = $author->getData('familyName')['en_US'];

            array_push($lastNames, $lastName);

            if (count($lastNames) == $maxAuthors) {
                break;
            }
        }

        $authorNamesString = join(", ", $lastNames);

        $message = sprintf(
            "%s %s. %s %s",
            __("plugins.generic.socialMedia.form.autoposter.message.article"),
            $authorNamesString,
            $title,
            $articleURL
        );

        return $message;
    }


    /**
     * Return a message for autoposting for an issue
     *
     * @param $issue
     *
     * @return string
     */
    function getMessageForIssue($issue) {
        $dispatcher = $this->request->getDispatcher();

        $editors = "";

        $issueURL = $dispatcher->url(
            $this->request,
            ROUTE_PAGE,
            null,
            'issue',
            'view',
            $issue->getId()
        );

        $message = sprintf(
            "%s %s | %s %s %s",
            __("plugins.generic.socialMedia.form.autoposter.message.issue"),
            $issue->getLocalizedTitle(),
            $editors,
            $this->request->getContext()->getLocalizedName(),
            $issue->getIssueSeries(),
            $issueURL
        );

        return $message;
    }


    /**
     * Override by subclass when a custom template should be provided for the
     * editing of a posting channel.
     *
     * Caution: The default form validators still apply.
     *
     * @return string
     */
    function getCustomFormTemplate() {
        return "";
    }


    /**
     * Return the template for the message queue
     *
     * @param $getArchive
     *
     * @return string
     */
    function getMessageQueueTemplate($getArchive = false) {
        $socialMediaPlugin = PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);
        $templatesPath = $socialMediaPlugin->getTemplatePath();


        $fullPath = join(DIRECTORY_SEPARATOR, [
            $templatesPath,
            ($getArchive) ? 'messageQueueArchive.tpl' : 'messageQueueForm.tpl'
        ]);

        return $fullPath;
    }


    /**
     * Return additional variables to pass into the MessageQueueFormTemplate
     *
     * @return array
     */
    function getAdditionalMessageQueueFormTemplateVariables() {
        return [];
    }


    /**
     * Helper function to assemble the url for the plugins icons
     *
     * @param $name Filename of the icon
     */
    abstract public function getIconURLForName($name);
}
