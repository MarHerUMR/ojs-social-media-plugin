{**
 * plugins/generic/socialMedia/templates/socialMediaSettingsForm.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * Form for social media setting
 *}

<script>
    $(function() {ldelim}
        // Attach the form handler.
        $('#socialMediaForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

{url|assign:actionUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.socialMedia.controllers.grid.SocialMediaGridHandler" op="updateSocialMediaSettings" escape=false}
<form class="pkp_form" id="socialMediaForm" method="post" action="{$actionUrl}">
    {csrf}
    {fbvFormArea id="socialMediaTags"}
        <legend>Social Media Meta Tags</legend>
        {fbvFormSection list="true"}
            {fbvElement type="checkbox" id="enableSocialMediaTags" label="plugins.generic.socialMedia.form.enableSocialMediaMetaTags" checked="$enableSocialMediaTags" value=1}
        {/fbvFormSection}
        {fbvFormSection}
            {fbvElement type="text" id="metaTagTwitterSite" label="plugins.generic.socialMedia.metaTagTwitterSite" value="$metaTagTwitterSite" placeholder="plugins.generic.socialMedia.metaTagTwitterSitePlaceholder" maxlength="20" size=$fbvStyles.size.SMALL}
            {if $additionalTagSettings}
                {foreach from=$additionalTagSettings item=setting}
                    {fbvElement type=$setting->type id=$setting->id label=$setting->label value=$setting->value placeholder=$setting->placeholder maxlength=$setting->maxlength size=$fbvStyles.size.SMALL}
                {/foreach}
            {/if}
        {/fbvFormSection}
    {/fbvFormArea}

    {fbvFormArea id="sidebarBlock"}
        <legend>Sidebar Block</legend>
        {fbvFormSection}
            {fbvElement type="text" id="blockTwitterAccount" label="plugins.generic.socialMedia.form.blockTwitterAccount" value="$blockTwitterAccount" placeholder="plugins.generic.socialMedia.metaTagTwitterSitePlaceholder" maxlength="20" size=$fbvStyles.size.SMALL}
            {fbvElement type="url" id="blockFacebookURL" label="plugins.generic.socialMedia.form.blockFacebookURL" value="$blockFacebookURL" placeholder="plugins.generic.socialMedia.form.blockFacebookURLPlaceholder" maxlength="200" size=$fbvStyles.size.SMALL urlValidationErrorMsg="plugins.generic.socialMedia.form.blockFacebookURL.urlError"}
            {if $additionalBlockSettings}
                {foreach from=$additionalBlockSettings item=setting}
                    {fbvElement type=$setting->type id=$setting->id label=$setting->label value=$setting->value placeholder=$setting->placeholder maxlength=$setting->maxlength size=$fbvStyles.size.SMALL}
                {/foreach}
            {/if}
        {/fbvFormSection}
    {/fbvFormArea}

    {fbvFormArea id="autoposting"}
        <legend>Autoposting</legend>
        {fbvFormSection list="true"}
            {fbvElement type="checkbox" id="enableAutoposting" label="plugins.generic.socialMedia.form.enableAutoposting" checked="$enableAutoposting" value=1}
        {/fbvFormSection}

    {/fbvFormArea}

    {capture assign=postingChannelsGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.socialMedia.controllers.grid.ManagePostingChannelGridHandler" op="fetchGrid" escape=false}{/capture}
    {load_url_in_div id="postingChannelsGridContainer" url=$postingChannelsGridUrl}

    {fbvElement type="submit" class="submitFormButton" id="submit" label="common.save"}
</form>
