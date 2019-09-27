{**
 * plugins/generic/socialMedia/templates/controllers/grid/form/addPostingChannelForm.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * Posting channel form to create posting channels.
 *}

 <script>
    $(function() {ldelim}
        // Attach the form handler.
        $('#postingChannelForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

<form class="pkp_form" id="postingChannelForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.socialMedia.controllers.grid.ManagePostingChannelGridHandler" op="addPostingChannelAction"}">
    {csrf}
    {fbvFormArea id="postingChannelInfo"}
        {fbvFormSection title="plugins.generic.socialMedia.form.autoposter.channelName" for="postingChannelName" required="true"}
            {fbvElement type="text" multilingual="true" id="postingChannelName" value=$postingChannelName maxlength="255" required="true"}
        {/fbvFormSection}

        {fbvFormSection title="common.type" for="postingChannelType" required="true"}
        {fbvElement type="select" id="postingChannelType" from=$postingChannelTypes selected=$selectedTypeId label="plugins.generic.socialMedia.form.autoposter.postingChannelTypeLabel" translate=false}
        {/fbvFormSection}
    {/fbvFormArea}
    {fbvFormButtons id="postingChannelFormSubmit" submitText="common.save"}
</form>