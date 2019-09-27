{**
 * plugins/generic/socialMedia/templates/messageQueue.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * The template for the message queue view
 *}

<script>
    $(function() {ldelim}
        $('#messageQueueTabs').pkpHandler('$.pkp.controllers.TabHandler');
    {rdelim});
</script>


<div id="messageQueueTabs">
    <ul>
        <li><a href="{url router=$smarty.const.ROUTE_COMPONENT op="showMsgQueue" postingChannelId=$postingChannelId}">{translate key="plugins.generic.socialMedia.form.autoposter.messageQueue"}</a></li>
        <li><a href="{url router=$smarty.const.ROUTE_COMPONENT op="showMsgArchive" postingChannelId=$postingChannelId}">{translate key="plugins.generic.socialMedia.form.autoposter.messageQueueArchive"}</a></li>
    </ul>
</div>
