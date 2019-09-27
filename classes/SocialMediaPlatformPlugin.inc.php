<?php
/**
 * @file plugins/generic/socialMedia/classes/SocialMediaPlatformPlugin.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class SocialMediaPlatformPlugin
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Abstract class for social media platform plugins
 */

abstract class SocialMediaPlatformPlugin {
    var $name;
    var $pluginPath;
    var $_templateManager;


    /**
     * Constructor
     */
    function __construct($args = []) {
        $this->socialMediaPlugin = PluginRegistry::getPlugin('generic', SOCIAL_MEDIA_PLUGIN_NAME);
        $this->request = Application::getRequest();
        $this->requestedOp = $this->request->_router->getRequestedOp($this->request);

        if ($args) {
            if ($args['pluginPath']) {
                $this->pluginPath = $args['pluginPath'];
            }

            if (array_key_exists('socialMediaPluginPath', $args)) {
                $this->socialMediaPluginPath = $args['socialMediaPluginPath'];
            } else {
                $this->socialMediaPluginPath = join(
                    DIRECTORY_SEPARATOR,
                    ["plugins", "generic", "socialMedia"]
                );
            }
        }

        $this->addLocaleData();
    }


    /**
     * Get the meta tags for the header
     *
     * @return array An array of tag objects
     */
    function getSocialMediaMetaTags() {
        return null;
    }


    /**
     * Return the title of the requested page showing the article galley
     *
     * @return string
     */
    function getArticleGalleyTitle() {
        $article = $this->getTemplateManager()->get_template_vars('article');

        $translationArray = [
            'key' => 'article.pageTitle',
            'title' => $article->getLocalizedTitle()
        ];

        $title = $this->getTemplateManager()->smartyTranslate(
            $translationArray,
            $this->getTemplateManager()
        );

        return $title;
    }


    /**
     * Get the data used of the sidebar block
     *
     * @return array
     */
    function getBlockData() {
        $blockData = array();

        return $blockData;
    }


    /**
     * Return the title of the requested issue
     *
     * @return string
     */
    function getIssuesArchiveTitle() {
        $args = $this->request->_router->getRequestedArgs($this->request);

        if (!empty($args)) {
            $translationArray = [
                'key' => 'archive.archivesPageNumber',
                'pageNumber' => $args[0]
            ];

            $pageTitle = $this->getTemplateManager()->smartyTranslate(
                $translationArray,
                $this->getTemplateManager()
            );
        } else {
            $pageTitle = __('archive.archives');
        }

        return $pageTitle;
    }


    /**
     * Geht the localized acronym of the journal
     *
     * @return string
     */
    function getJournalAcronym() {
        return $this->request->getJournal()->getLocalizedAcronym();
    }


    /**
     * Get the url, alt text, width and height of the journal header image
     *
     * @return array
     */
    function getJournalHeaderImage() {
        $journalHeaderImage = array(
            "url" => "",
            "altText" => ""
        );

        $publicFilesDir = $this->getTemplateManager()->get_template_vars('publicFilesDir');
        $headerImage = $this->getTemplateManager()->get_template_vars('displayPageHeaderLogo');

        if ($headerImage['uploadName']) {
            $journalHeaderImage['url'] = $publicFilesDir . "/" . $headerImage['uploadName'];

            if ($headerImage['altText']) {
                $journalHeaderImage['altText'] = $headerImage['altText'];
            }

            $journalHeaderImage['width'] = $headerImage['width'];
            $journalHeaderImage['height'] = $headerImage['height'];
        }

        return $journalHeaderImage;
    }


    /**
     * Get the localized journal name
     *
     * @return string
     */
    function getJournalName() {
        return $this->getTemplateVar("currentContext")->getLocalizedName();
    }


    /**
     * Get the settings from the social media DAO
     */
    function getSettings() {
        $socialMediaDAO = $this->socialMediaPlugin->getSocialMediaDAO();
        $this->settings = $socialMediaDAO->getSettingsByContextId($this->contextId);
    }

    /**
     * Get the description for the requested page and op
     *
     * @return string
     */
    function getSiteDescription() {
        import('lib.pkp.classes.core.PKPString');

        $requestedPage = $this->request->_router->getRequestedPage($this->request);
        $description = "";

        switch ($requestedPage) {
            case 'index':
                $description = $this->translateAndReplace(
                    'plugins.generic.socialMedia.description.index',
                    array(
                        "-journalName-" => $this->getJournalName(),
                        "-currentIssue-" => __('journal.currentIssue'),
                        "-currentIssueId-" => $this->getTemplateVar('issue')->getIssueIdentification()
                    )
                );
                break;

            case '':
                $description = $this->translateAndReplace(
                    'plugins.generic.socialMedia.description.siteIndex',
                    array(
                        "-siteName-" => $this->request->getSite()->getLocalizedTitle(),
                    )
                );
                break;

            case 'article':
                if ($this->requestedOp == "view") {
                    $requestArgs = $this->request->getRouter()->getRequestedArgs($this->request);

                    if (count($requestArgs) == 1){
                        $abstract = $this->getTemplateVar('article')->getLocalizedAbstract();
                        $description = strip_tags($abstract);
                    }

                    // $requestArgs is 2 when the galley is most likley to be shown
                    if (count($requestArgs) == 2){
                        $abstract = $this->getTemplateVar('article')->getLocalizedAbstract();
                        $galleyLabel = $this->getTemplateVar('galley')->getLabel();
                        $description = "[$galleyLabel] " . strip_tags($abstract);
                    }
                }
                break;

            case 'issue':
                if ($this->requestedOp == "view") {
                    $description = $this->translateAndReplace(
                        "plugins.generic.socialMedia.description.issue",
                        array(
                            "-journalAcronym-" => $this->getJournalAcronym(),
                            "-title-" => $this->getTemplateVar('issueIdentification')
                        )
                    );
                }

                if ($this->requestedOp == "archive") {
                    $description = $this->translateAndReplace(
                        'plugins.generic.socialMedia.description.issuearchive',
                        array("-journalAcronym-" => $this->getJournalAcronym())
                    );
                }
                break;

            case 'search':
                if ($this->requestedOp == "search" OR $this->requestedOp == "index") {
                    $description = $this->translateAndReplace(
                        'plugins.generic.socialMedia.description.search',
                        array("-journalAcronym-" => $this->getJournalAcronym())
                    );
                }

                if ($this->requestedOp == "authors") {
                    $requestArgs = $this->request->getRouter()->getRequestedArgs($this->request);

                    if (count($requestArgs) == 0) {
                        $description = $this->translateAndReplace(
                            'plugins.generic.socialMedia.description.authorsIndex',
                            array("-journalAcronym-" => $this->getJournalAcronym())
                        );
                    }

                    // $requestArgs is 1 when authors details are shown
                    if (count($requestArgs) == 1) {
                        $firstName = $this->request->getUserVar('firstName');
                        $middleName = $this->request->getUserVar('middleName');
                        $lastName = $this->request->getUserVar('lastName');

                        $authorName = $firstName . " " . ($middleName ? $middleName . " " : "") . $lastName;

                        $description = $this->translateAndReplace('plugins.generic.socialMedia.description.authorsDetails', array(
                            "-authorName-" => $authorName,
                            "-journalAcronym-" => $this->getJournalAcronym()
                        ));
                    }
                }
                break;

            case 'about':
                if ($this->requestedOp == "aboutThisPublishingSystem") {
                    $description = $this->translateAndReplace(
                        'plugins.generic.socialMedia.description.aboutThisPublishingSystem',
                        array("-journalAcronym-" => $this->getJournalAcronym())
                    );
                }

                if ($this->requestedOp == "contact") {
                    $description = __('plugins.generic.socialMedia.description.contact');
                }

                if ($this->requestedOp == "editorialTeam") {
                    $description = $this->translateAndReplace(
                        'plugins.generic.socialMedia.description.editorialTeam',
                        array("-journalAcronym-" => $this->getJournalAcronym())
                    );
                }

                if ($this->requestedOp == "index") {
                    $description = $this->translateAndReplace(
                        'plugins.generic.socialMedia.description.aboutIndex',
                        array("-journalAcronym-" => $this->getJournalAcronym())
                    );
                }

                if ($this->requestedOp == "submissions") {
                    $description = $this->translateAndReplace(
                        'plugins.generic.socialMedia.description.submissions',
                        array("-journalAcronym-" => $this->getJournalAcronym())
                    );
                }
                break;

            case 'information':
                switch ($this->requestedOp) {
                    case 'readers':
                        $description = $this->translateAndReplace(
                            'plugins.generic.socialMedia.description.information.informationForReaders',
                            array("-journalName-" => $this->getJournalName())
                        );
                        break;

                    case 'authors':
                        $description = $this->translateAndReplace(
                            'plugins.generic.socialMedia.description.information.informationForAuthors',
                            array("-journalName-" => $this->getJournalName())
                        );
                        break;

                    case 'librarians':
                        $description = $this->translateAndReplace(
                            'plugins.generic.socialMedia.description.information.informationForLibrarians',
                            array("-journalName-" => $this->getJournalName())
                        );
                        break;

                    case 'competingInterestGuidelines':
                        $description = $this->translateAndReplace(
                            'plugins.generic.socialMedia.description.information.competingInterestGuidelines',
                            array("-journalAcronym-" => $this->getJournalAcronym())
                        );
                        break;

                    case 'sampleCopyrightWording':
                        $description = __('plugins.generic.socialMedia.description.information.sampleCopyrightWording');
                        break;
                }
                break;

            case 'announcement':
                if ($this->requestedOp == "index") {
                    $description = __('plugins.generic.socialMedia.description.announcements.index');
                }

                if ($this->requestedOp == "view") {
                    $description = $this->getTemplateVar('announcement')->getLocalizedDescriptionShort();
                }
                break;

            case 'indexJournal':
                throw new Exception("Not implemented", 1);
                // $description = $this->getFullTitleForKey('about');
                break;

            case 'login':
                if ($this->requestedOp == "index") {
                    $description = __('plugins.generic.socialMedia.description.login.index');
                }

                if ($this->requestedOp == "lostPassword") {
                    $description = __('plugins.generic.socialMedia.description.login.lostPassword');
                }
                break;

            case 'user':
                if ($this->requestedOp == "register") {
                    $description = $this->translateAndReplace(
                        'plugins.generic.socialMedia.description.register',
                        array("-journalName-" => $this->getJournalName())
                    );
                }
                break;
        }

        $description = PKPString::html2text($description);
        $description = trim($description);
        $description = htmlspecialchars_decode($description);
        $description = PKPString::regexp_replace('/\"/', "&quot;", $description);

        return $description;
    }


    /**
     * Get the full title for the requested page and op
     *
     * @return string
     */
    function getPageTitle() {
        $requestedPage = $this->request->_router->getRequestedPage($this->request);

        switch ($requestedPage) {
            case 'index':
                return $this->getBareSiteTitle();
                break;

            case 'article':
                if ($this->requestedOp == "view") {
                    $requestArgs = $this->request->getRouter()->getRequestedArgs($this->request);

                    if (count($requestArgs) == 1) {
                        $unescapedTitle = $this->getTemplateManager()->get_template_vars('article')->getLocalizedTitle();
                        $articleTitle = $this->getTemplateManager()->smartyEscape($unescapedTitle);

                        $lineStart = $articleTitle;
                    }

                    // $requestArgs is 2 when the galley is most likley to be shown
                    if (count($requestArgs) == 2) {
                        $lineStart = $this->getArticleGalleyTitle();
                    }
                }
                break;

            case 'issue':
                if ($this->requestedOp == "view") {
                    $lineStart = $this->getTemplateManager()->get_template_vars('issue')->getIssueIdentification();
                }

                if ($this->requestedOp == "archive") {
                    $lineStart = $this->getIssuesArchiveTitle();
                }
                break;

            case 'search':
                if ($this->requestedOp == "search" OR $this->requestedOp == "index") {
                    $lineStart = __('common.search');
                }

                if ($this->requestedOp == "authors") {
                    $requestArgs = $this->request->getRouter()->getRequestedArgs($this->request);

                    if (count($requestArgs) == 0){
                        $lineStart = __('search.authorIndex');
                    }

                    // $requestArgs is 1 when authors details are shown
                    if (count($requestArgs) == 1) {
                        $lineStart = __('search.authorDetails');
                    }
                }
                break;

            case 'about':
                if ($this->requestedOp == "contact") {
                    $lineStart = __('about.contact');
                }

                if ($this->requestedOp == "editorialTeam") {
                    $lineStart = __('about.editorialTeam');
                }

                if ($this->requestedOp == "index") {
                    $lineStart = __('about.aboutContext');
                }

                if ($this->requestedOp == "submissions") {
                    $lineStart = __('about.submissions');
                }

                if ($this->requestedOp == "aboutThisPublishingSystem") {
                    $lineStart = __('about.aboutThisPublishingSystem');
                }
                break;

            case 'information':
                switch ($this->requestedOp) {
                    case 'readers':
                        $lineStart = __('navigation.infoForReaders.long');
                        break;

                    case 'authors':
                        $lineStart = __('navigation.infoForAuthors.long');
                        break;

                    case 'librarians':
                        $lineStart = __('navigation.infoForLibrarians.long');
                        break;

                    case 'competingInterestGuidelines':
                        $lineStart = __('navigation.competingInterestGuidelines');
                        break;

                    case 'sampleCopyrightWording':
                        $lineStart = __('manager.setup.copyrightNotice');
                        break;

                    default:
                        $lineStart = $this->getPageTitle();
                        break;
                }
                break;

            case 'announcement':
                if ($this->requestedOp == "index") {
                    $lineStart = __('announcement.announcements');
                }

                if ($this->requestedOp == "view") {
                    $lineStart = $this->getTemplateManager()->get_template_vars('announcement')->getLocalizedTitle();
                }
                break;

            case '':
                $site = $this->request->getSite();
                return $site->getLocalizedTitle();
                break;

            case 'login':
                if ($this->requestedOp == "index") {
                    $lineStart = __('user.login');
                }

                if ($this->requestedOp == "lostPassword") {
                    $lineStart = __('user.login.resetPassword');
                }
                break;

            case 'user':
                if ($this->requestedOp == "register") {
                    $lineStart = __('user.register');
                }
                break;

            default:
                return $this->getBareSiteTitle();
                break;
        }

        return $lineStart . " | " . $this->getBareSiteTitle();
    }


    /**
     * Get the url, alt text, width and height of an image to represent the current page/view
     *
     * @return array
     */
    function getPageImage() {
        $requestedPage = $this->request->_router->getRequestedPage($this->request);
        $pageImage = array(
            "url" => "",
            "altText" => ""
        );

        switch ($requestedPage) {
            case 'article':
                // Trying article cover first, issue cover second, header image at last
                $articleCoverURL = $this->getTemplateManager()->get_template_vars('article')->getLocalizedCoverImageUrl();

                if ($articleCoverURL) {
                    $article = $this->getTemplateManager()->get_template_vars('article');

                    $pageImage['url'] = $articleCoverURL;
                    $pageImage['altText'] = $article->getLocalizedCoverImageAltText();

                    $size = $this->getArticleCoverSize($article);
                    $pageImage['width'] = $size['width'];
                    $pageImage['height'] = $size['height'];
                    break;
                }

                $issueCoverURL = $this->getTemplateManager()->get_template_vars('issue')->getLocalizedCoverImageUrl();

                if ($issueCoverURL){
                    $issue = $this->getTemplateManager()->get_template_vars('issue');

                    $pageImage['url'] = $issueCoverURL;
                    $pageImage['altText'] = $issue->getLocalizedCoverImageAltText();

                    $size = $this->getIssueCoverSize($issue);
                    $pageImage['width'] = $size['width'];
                    $pageImage['height'] = $size['height'];
                    break;
                }

                $pageImage = $this->getJournalHeaderImage();
                break;

            case 'issue':
                if ($this->requestedOp == "view") {
                    $issueCoverURL = $this->getTemplateManager()->get_template_vars('issue')->getLocalizedCoverImageUrl();

                    if ($issueCoverURL) {
                        $issue = $this->getTemplateManager()->get_template_vars('issue');

                        $pageImage['url'] = $issueCoverURL;
                        $pageImage['altText'] = $issue->getLocalizedCoverImageAltText();
                        $size = $this->getIssueCoverSize($issue);

                        $pageImage['width'] = $size['width'];
                        $pageImage['height'] = $size['height'];
                        break;
                    } else {
                        $pageImage = $this->getJournalHeaderImage();
                        break;
                    }
                } else{
                    $pageImage = $this->getJournalHeaderImage();
                }

                break;

            case 'index':
                $issueCoverURL = $this->getTemplateManager()->get_template_vars('issue')->getLocalizedCoverImageUrl();

                if ($issueCoverURL) {
                    $issue = $this->getTemplateManager()->get_template_vars('issue');

                    $pageImage['url'] = $issueCoverURL;
                    $pageImage['altText'] = $issue->getLocalizedCoverImageAltText();
                    $size = $this->getIssueCoverSize($issue);

                    $pageImage['width'] = $size['width'];
                    $pageImage['height'] = $size['height'];
                    break;
                } else {
                    $pageImage = $this->getJournalHeaderImage();
                    break;
                }
                break;

            case '':
                $site = $this->request->getSite();
                $locale = AppLocale::getLocale();
                $titleImage = $site->getSetting('pageHeaderTitleImage');

                if (isset($titleImage[$locale]) && $titleImage[$locale] != null) {
                    $fileName = $titleImage[$locale]['uploadName'];
                    $pageImage['url'] = join(DIRECTORY_SEPARATOR, [$this->request->getBaseUrl(), Config::getVar('files', 'public_files_dir'), 'site', $fileName]);
                    $pageImage['width'] = $titleImage[$locale]['width'];
                    $pageImage['height'] = $titleImage[$locale]['height'];
                    $pageImage['altText'] = $titleImage[$locale]['altText'];
                }


            default:
               $pageImage = $this->getJournalHeaderImage();
               break;
        }

        return $pageImage;
    }


    /**
     * Get the size width an height of issue cover image
     *
     * @param $issue
     *
     * @return array
     */
    function getIssueCoverSize($issue) {
        return $this->getCoverSizeForIssueOrArticle($issue);
    }


    /**
     * Get the size width an height of article cover image
     *
     * @param $article
     *
     * @return array
     */
    function getArticleCoverSize($article) {
        return $this->getCoverSizeForIssueOrArticle($article);
    }


    /**
     * Get the size width an height of article/issue cover image
     *
     * @param $objectWithCover
     *
     * @return array
     */
    function getCoverSizeForIssueOrArticle($objectWithCover) {
        $siteImage['width'] = $objectWithCover->getLocalizedData('width');
        $siteImage['height'] = $objectWithCover->getLocalizedData('height');

        $baseUrl = $this->request->getBaseUrl();

        $coverPath = substr($objectWithCover->getLocalizedCoverImageUrl(), strlen($baseUrl) + 1);
        $size = getimagesize($coverPath);

        return array(
            'width' => $size[0],
            'height' => $size[1]
        );
    }


    /**
     * Get the social media tag by fetching the template
     *
     * @param $name string
     * @param $content string
     *
     * @return array
     */
    function getSocialMediaTag($name, $content) {
        $templateManager = $this->getTemplateManager();

        $templateManager->assign("key", $name);
        $templateManager->assign("value", $content);

        $content = $templateManager->fetch($this->getMetaTagTemplatePath());

        $templateManager->clear_assign(array("key", "value"));

        return array("name" => $name, "content" => $content);
    }


    /**
     * Return the TemplateManager for the current request
     *
     * @return TemplateManager
     */
    function getTemplateManager() {
        if (empty($_templateManager)) {
            $this->_templateManager = TemplateManager::getManager($this->request);
        }

        return $this->_templateManager;
    }


    /**
     * Return the content of the template variable
     *
     * @param string key
     *
     * @return TemplateManager
     */
    function getTemplateVar($key) {
        return $this->getTemplateManager()->get_template_vars($key);
    }


    /**
     * Return the canonical template path of this plug-in
     *
     * @return string
     */
    function getTemplatePath() {
        $basePath = Core::getBaseDir();

        return "file:$basePath" . DIRECTORY_SEPARATOR . $this->pluginPath . "templates" . DIRECTORY_SEPARATOR;
    }


    /**
     * Translate the key and replace the needle
     *
     * @param $key string
     * @param $replacements array replacements with key as the $search and value as the $replace
     *
     * @return string
     */
    function translateAndReplace($key, $replacements) {
        $output = __($key);

        foreach ($replacements as $search => $replace) {
            $output = str_replace($search, $replace, $output);
        }

        return $output;
    }


    /**
     * Return the site title
     *
     * @return string
     */
    function getBareSiteTitle() {
        $siteName = $this->request->getContext()->getLocalizedName();

        return $siteName;
    }


    /**
     * Load locale data for this plugin.
     *
     * @param $locale string
     *
     * @return boolean
     */
    function addLocaleData($locale = null) {
        if ($locale == '') $locale = AppLocale::getLocale();

        $localeFilenames = $this->getLocaleFilename($locale);

        if ($localeFilenames) {
            if (is_scalar($localeFilenames)) $localeFilenames = array($localeFilenames);

            foreach($localeFilenames as $localeFilename) {
                AppLocale::registerLocaleFile($locale, $localeFilename);
            }

            return true;
        }

        return false;
    }


    /**
     * Get the filename for the locale data for this plugin.
     *
     * @param $locale string
     *
     * @return array the locale file names
     */
    function getLocaleFilename($locale) {
        $baseLocaleFilename = $this->getPluginPath() . "/locale/$locale/locale.xml";
        $filenames = array();
        if (file_exists($baseLocaleFilename)) $filenames[] = $baseLocaleFilename;
        return $filenames;
    }


    /**
     * Get the path this plugin's files are located in.
     *
     * @return String pathname
     */
    function getPluginPath() {
        return $this->pluginPath;
    }


    /**
     * Platform plugins should return true if they support autoposting
     *
     * @return boolean
     */
    function supportsAutoposting() {
        return false;
    }
}

?>
