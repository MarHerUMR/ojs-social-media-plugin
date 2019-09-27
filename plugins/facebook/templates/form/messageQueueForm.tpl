{**
 * plugins/generic/socialMedia/plugins/facebook/templates/form/messageQueueForm.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * The template for the facebook message queue form
 *}

<script>
    $(function() {ldelim}
        // Attach the form handler.
        $('#messageQueue').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

{url|assign:actionUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.socialMedia.controllers.grid.MessageQueueGridHandler" op="updateMessage" escape=false}
<form class="pkp_form" id="messageQueue" method="post" action="{$actionUrl}">
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
    {capture assign=messageQueueGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.socialMedia.plugins.facebook.controllers.grid.FacebookMessageQueueGridHandler" op="fetchQueueGrid" escape=false postingChannelId=$postingChannelId}{/capture}
    {load_url_in_div id="messageQueueGridContainer" url=$messageQueueGridUrl}

    {if $facebookCookieConsent}
    {capture assign=scheduleGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.socialMedia.plugins.facebook.controllers.grid.FacebookMessageQueueGridHandler" op="fetchScheduleGrid" escape=false postingChannelId=$postingChannelId pageId=$pageId}{/capture}
    {load_url_in_div id="messageQueueScheduleGridContainer" url=$scheduleGridUrl}
    {/if}
    <input type="hidden" value="{$fbPageId}" id="pageId">
    <input type="hidden" value="{$fbAppId}" id="appId">
    <input type="hidden" value="{$frequency}" id="frequency">
</form>

{if $facebookCookieConsent}
<script src="{$viewControllerURL}" type="text/javascript"></script>
{/if}
