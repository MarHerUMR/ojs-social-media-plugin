<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/PostingChannelGridCellProvider.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class PostingChannelGridCellProvider
 * @ingroup controllers_grid_postingChannels
 *
 * @brief Cell provider for title column of posting channel type grid.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class PostingChannelGridCellProvider extends GridCellProvider {
    /**
     * Extracts variables for a given column from a data element
     * so that they may be assigned to template before rendering.
     *
     * @param $row GridRow
     * @param $column GridColumn
     *
     * @return array
     */
    function getTemplateVarsFromRowColumn($row, $column) {
        $postingChannel = $row->getData();
        $columnId = $column->getId();

        switch ($columnId) {
            case 'channelName':
                return ['label' => $postingChannel->getData('channelName')];
                break;

            case 'type':
                $channelType = $postingChannel->getData('channelType');
                $socialMediaPlugin = PluginRegistry::getPlugin(
                    'generic',
                    SOCIAL_MEDIA_PLUGIN_NAME
                );

                $platformPlugin = $socialMediaPlugin->getSocialMediaPlatformPluginForPostingChannelType($channelType);

                return ['label' => $platformPlugin->getPostingChannelTypeName()];
                break;

            case 'msgInQueue':
                $messageQueue = $postingChannel->getMessageQueue();
                $messageCount = $messageQueue->getUnpostedMessageCount();
                return ['label' => $messageCount];
                break;

            case 'msgQueue':
                return ['label' => ''];
                break;

            default:
                break;
        }

        return parent::getTemplateVarsFromRowColumn($row, $column);
    }

   /**
    * @copydoc GridCellProvider::getCellActions()
    */
   function getCellActions($request, $row, $column, $position = GRID_ACTION_POSITION_DEFAULT) {
        $router = $request->getRouter();
        $actionArgs = [
            'postingChannelId' => $row->getId()
        ];

        switch ($column->getId()) {
            case 'msgQueue':
                import('lib.pkp.classes.linkAction.request.AjaxModal');

                $modalTitle = join(" ", [
                    __('plugins.generic.socialMedia.form.autoposter.messageQueueOf'),
                    $row->getData()->getData('channelName')
                ]);

                return [
                    new LinkAction(
                        'showQueue',
                        new AjaxModal(
                            $router->url(
                                $request,
                                null,
                                null,
                                'showMsgQueueView',
                                null,
                                $actionArgs
                            ),
                            $modalTitle,
                            null,
                            true
                        ),
                        __('plugins.generic.socialMedia.form.autoposter.showQueue')
                    )
                ];
                break;
        }

       return parent::getCellActions($request, $row, $column, $position);
   }
}

?>
