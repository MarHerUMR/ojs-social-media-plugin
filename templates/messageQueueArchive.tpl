{**
 * plugins/generic/socialMedia/templates/messageQueueArchive.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * The template for the message queue archive
 *}

<script>
    $(function() {ldelim}
        // Attach the form handler.
        $('#messageQueueArchive').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

<form class="pkp_form" id="messageQueueArchive" method="post" action="">
    {capture assign=messageArchiveGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.socialMedia.controllers.grid.MessageQueueGridHandler" op="fetchArchiveGrid" escape=false postingChannelId=$postingChannelId}{/capture}
    {load_url_in_div id="messageArchiveGridContainer" url=$messageArchiveGridUrl}
</form>
