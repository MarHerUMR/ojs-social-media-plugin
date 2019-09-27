{**
 * plugins/generic/socialMedia/templates/block.tpl
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * The sidebar block template
 *}
<div class="pkp_block" id="$customBlockId|escape">
    <div class="content">
        <span class="title">Social Media</span>
        {foreach from=$blockData item=pluginData}
            {if !empty($pluginData)}
                <div class="social-media-service {$pluginData.serviceName}">
                    {if !$hideServiceName}<span class="title">{$pluginData.serviceName}</span>{/if}
                    <p>{$pluginData.content}</p>
                </div>
            {/if}
        {/foreach}
    </div>
</div>
