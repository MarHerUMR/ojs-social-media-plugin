{**
 * plugins/generic/socialMedia/templates/messageQueueForm.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * The template for the message queue form
 *}

<script>
    $(function() {ldelim}
        // Attach the form handler.
        $('#messageQueue').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

<form class="pkp_form" id="messageQueue" method="post" action="">
    {capture assign=messageQueueGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.socialMedia.controllers.grid.MessageQueueGridHandler" op="fetchQueueGrid" escape=false postingChannelId=$postingChannelId}{/capture}
    {load_url_in_div id="messageQueueGridContainer" url=$messageQueueGridUrl}
</form>
