{**
 * plugins/generic/SocialMedia/plugins/twitter/templates/twitterOauthSuccess.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * The view with the Twitter credentials returned from the API
 *}

{include file="frontend/components/header.tpl" pageTitleTranslated=$pageTitle}
<h2>{translate key="plugins.generic.socialMedia.autoposter.twitter.callbackPage.successHeading"}</h2>
<p>{translate key="plugins.generic.socialMedia.autoposter.twitter.callbackPage.successUsername"} {$username}.</p>
<p>{translate key="plugins.generic.socialMedia.autoposter.twitter.callbackPage.successParagraph"}</p>
<p>{translate key="plugins.generic.socialMedia.autoposter.twitter.callbackPage.accessToken"}: {$accessToken}</p>
<p>{translate key="plugins.generic.socialMedia.autoposter.twitter.callbackPage.accessTokenSecret"}: {$accessTokenSecret}</p>
{include file="frontend/components/footer.tpl"}
