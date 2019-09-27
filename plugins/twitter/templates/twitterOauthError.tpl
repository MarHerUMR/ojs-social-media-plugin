{**
 * plugins/generic/socialMedia/plugins/twitter/templates/twitterOauthError.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * A basic view to display error messages from the Twitter oauth flow
 *
 *}

{include file="frontend/components/header.tpl" pageTitleTranslated=$pageTitle}
<h2>{$heading}</h2>
<p>{$paragraph}</p>
{include file="frontend/components/footer.tpl"}
