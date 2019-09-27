{**
 * plugins/generic/socialMedia/templates/controllers/grid/form/editPostingChannelForm.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * Form for the posting channel settings
 *}

<script>
    $(function() {ldelim}
        // Attach the form handler.
        $('#postingChannelsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

<form class="pkp_form" id="postingChannelsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.socialMedia.controllers.grid.ManagePostingChannelGridHandler" op="updatePostingChannel"}">
    {csrf}
    {fbvFormArea id="baseSettings"}
        {if $postingChannelId}
            <input type="hidden" name="postingChannelId" value="{$postingChannelId|escape}" />
        {/if}
        {fbvFormSection list="true" title="plugins.generic.socialMedia.form.autoposter.generalSettings"}
            {fbvElement type="checkbox" id="postingChannelActive" label="common.active" checked="$postingChannelActive" value=1 size=$fbvStyles.size.MEDIUM}
            {fbvElement type="text" id="postingChannelName" label="plugins.generic.socialMedia.form.autoposter.channelName" value="$postingChannelName" placeholder="" maxlength="100" size=$fbvStyles.size.MEDIUM required=true}

            {fbvElement type="select" id="postingChannelType" label="plugins.generic.socialMedia.form.autoposter.postingChannelTypeLabel" disabled="disabled" translate=false from=$postingChannelTypeLabel size=$fbvStyles.size.MEDIUM}

            {fbvElement
                type="text"
                id="postingChannelFrequency"
                label="plugins.generic.socialMedia.form.autoposter.postingChannelFrequency"
                value="$postingChannelFrequency"
                placeholder="plugins.generic.socialMedia.form.autoposter.postingChannelFrequencyPlaceholder"
                maxlength="3"
                size=$fbvStyles.size.MEDIUM
            }
        {/fbvFormSection}
    {/fbvFormArea}

    {if $additionalSettings}
    {fbvFormArea id="platformSpecificSettings"}
            {capture assign="additionalSettingsTitle"}{translate key="plugins.generic.socialMedia.form.autoposter.additionalSettings"} {$postingChannelTypeLabel}{/capture}
        {fbvFormSection title=$additionalSettingsTitle translate=false}
            {foreach from=$additionalSettings item=setting}
                {fbvElement type=$setting->type id=$setting->id label=$setting->label value=$setting->value placeholder=$setting->placeholder maxlength=$setting->maxlength size=$fbvStyles.size.MEDIUM required=$setting->required}
            {/foreach}
        {/fbvFormSection}
    {/fbvFormArea}
    {/if}

    {if $additionalContent}
        {$additionalContent}
    {/if}
    <p><span class="formRequired">{translate key="common.requiredField"}</span></p>
    {fbvFormButtons id="postingChannelFormSubmit" submitText="common.save"}
</form>
