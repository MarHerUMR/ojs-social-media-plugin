<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/PostingChannelGridRow.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class PostingChannelGridRow
 * @ingroup controllers_grid_postingChannels
 *
 * @brief PostingChannel grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');
import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');

class PostingChannelGridRow extends GridRow {
    //
    // Overridden methods from GridRow
    //
    /**
     * @copydoc GridRow::initialize()
     */
    function initialize($request, $template = null) {
        parent::initialize($request, $template);
        $element = $this->getData();

        $rowId = $this->getId();
        // Is this a new row or an existing row?
        if (!empty($rowId) && is_numeric($rowId)) {
            // Only add row actions if this is an existing row
            $router = $request->getRouter();
            $actionArgs = array(
                'postingChannelId' => $rowId
            );

            $this->addAction(
                new LinkAction(
                    'edit',
                    new AjaxModal(
                        $router->url($request, null, null, 'editPostingChannel', null, $actionArgs),
                        __('grid.action.edit'),
                        'modal_edit',
                        true
                        ),
                    __('grid.action.edit'),
                    'edit')
            );

            $this->addAction(
                new LinkAction(
                    'remove',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('common.confirmDelete'),
                        __('common.remove'),
                        $router->url($request, null, null, 'deletePostingChannel', null, $actionArgs),
                        'modal_delete'
                        ),
                    __('grid.action.remove'),
                    'delete')
            );
        }
    }
}

?>
