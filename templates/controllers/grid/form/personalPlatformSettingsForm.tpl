{**
 * plugins/generic/socialMedia/controllers/grid/form/personalPlatformSettingsForm.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * Form for the personal platform settings
 *}
<script>
    $(function() {ldelim}
        // Attach the form handler.
        $('#platformSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

<h4>Settings of {$fullName} ({$userName})</h4>

<form class="pkp_form" id="platformSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.socialMedia.controllers.grid.ManagePostingChannelGridHandler" op="updatePersonalPlatformSettings"}">
    {csrf}
    {fbvFormArea id="baseSettings"}
        {fbvFormSection list="true" title="plugins.generic.socialMedia.form.autoposter.thirdPartyCookies"}
            {fbvElement type="checkbox" id="facebookCookieConsent" label="plugins.generic.socialMedia.form.autoposter.facebookCookies" checked="$facebookCookieConsent" value=1 size=$fbvStyles.size.LARGE}
        {/fbvFormSection}
    {/fbvFormArea}

    <p><span class="formRequired">{translate key="common.requiredField"}</span></p>
    {fbvFormButtons id="postingChannelFormSubmit" submitText="common.save"}
</form>