{**
 * plugins/socialMedia/plugins/facebook/templates/form/editPostingChannelForm.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * Form for the facebook posting channel settings
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

    {fbvFormArea id="fbSettings"}
    <legend>
        {translate key='plugins.generic.socialMedia.form.autoposter.facebookSettings'}
    </legend>
    <div class="pkp_controllers_grid" id="facebookStatus">
        <div class="header">
            <h4>Facebook Status</h4>
            <ul class="actions">
                <li><div id="fbButtonContainer"></li>
            </ul>
        </div>
        <table>
            <tbody>
                <tr class="gridRow">
                    <td class="first_column">
                        <span class="gridCellContainer">
                            {if $facebookCookieConsent}
                                <span id="statusIndicator" class="fa messageWarning" aria-hidden="true"></span><span id="statusMessage" class="label">&nbsp;</span>
                            {else}
                                <span class="label">{translate key="plugins.generic.socialMedia.form.autoposter.facebookCookieConsent"}</span><br>
                                {include file="linkAction/linkAction.tpl" action=$platformSettingsAction contextId=""}
                            {/if}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
        {fbvFormSection list="true" title="plugins.generic.socialMedia.form.autoposter.fbAppId"}
            {fbvElement
                type="text"
                id="fbAppId"
                label="plugins.generic.socialMedia.form.autoposter.fbAppId"
                value="$fbAppId"
                placeholder="plugins.generic.socialMedia.form.autoposter.fbAppIdPlaceholder"
                maxlength="20"
                size=$fbvStyles.size.MEDIUM
            }
        {/fbvFormSection}

        {fbvFormSection for="fbUsername" title="plugins.generic.socialMedia.form.autoposter.fbUsername"}
            {fbvElement type="text" id="fbUsername" label="plugins.generic.socialMedia.form.autoposter.fbUsernameInstruction" value="" placeholder="" maxlength="100" disabled="disabled" size=$fbvStyles.size.MEDIUM}
        {/fbvFormSection}

        {fbvFormSection list="true" for="fbPageId" title="plugins.generic.socialMedia.form.autoposter.fbPageNameSelect"}
            {fbvElement
                type="select"
                id="fbPageId"
                disabled="disabled"
                label="plugins.generic.socialMedia.form.autoposter.fbPageNameSelectInstruction"
                translate=false size=$fbvStyles.size.MEDIUM
            }
        {/fbvFormSection}
    {/fbvFormArea}

    <p><span class="formRequired">{translate key="common.requiredField"}</span></p>
    {fbvFormButtons id="postingChannelFormSubmit" submitText="common.save"}
    <input type="hidden" value="{$fbPageId}" id="pageId">
</form>


{if $facebookCookieConsent}
    <script src="{$viewControllerURL}" type="text/javascript"></script>
{/if}
