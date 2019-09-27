<?php
/**
 * @file plugins/generic/socialMedia/classes/SocialMediaDAO.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class SocialMediaDAO
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Operations for retrieving and modifying social media settings.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.socialMedia.classes.SocialMediaSettings');
import('classes.article.ArticleDAO');

class SocialMediaDAO extends DAO {
    /**
     * Constructor.
     */
    function __construct() {
        parent::__construct();

        $this->_additionalFieldNames = array();
    }


    /**
     * Get a list of additional fields that do not have
     * dedicated accessors.
     *
     * @return array
     */
    function getAdditionalFieldNames() {
        return array_merge(
            parent::getAdditionalFieldNames(),
            $this->_additionalFieldNames,
            array(
                'enableSocialMediaTags',
                'enableAutoposting',
                'metaTagTwitterSite',
                'blockTwitterAccount',
                'blockFacebookURL'
            )
        );
    }


    /**
     * Add additional fields that do not have
     * dedicated accessors.
     *
     * @param $additionalFieldNames array
     */
    function addAdditionalFieldNames($additionalFieldNames) {
        if (!is_array($additionalFieldNames)) {
            array_push($this->_additionalFieldNames, $additionalFieldNames);
        } else {
            array_merge($this->_additionalFieldNames, $additionalFieldNames);
        }
    }

    /**
     * Retrieve settings by context id
     *
     * @param $contextId int
     *
     * @return array
     */
    function getSettingsByContextId($contextId){
        $settings = $this->newDataObject();

        $settings->contextId = $contextId;

        $this->getDataObjectSettings('social_media_settings', 'context_id', $contextId, $settings);

        return $settings;
    }


    /**
     * Update the database with social media settings
     *
     * @param $socialMediaSettings SocialMediaSettings
     */
    function updateSettings($socialMediaSettings) {
        $this->updateDataObjectSettings('social_media_settings', $socialMediaSettings, [
            'context_id' => $socialMediaSettings->contextId
        ]);
    }


    /**
     * Construct a new data object corresponding to this DAO.
     *
     * @return SocialMediaSettings
     */
    function newDataObject() {
        return new SocialMediaSettings();
    }
}

?>
