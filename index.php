<?php
/**
 * @defgroup plugins_generic_socialMedia Social Media Plugin
 */

/**
 * @file plugins/generic/socialMedia/index.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @ingroup plugins_generic_socialMedia
 * @brief Wrapper for social media plugin.
 *
 */

require_once('SocialMediaPlugin.inc.php');

return new SocialMediaPlugin();
?>
