<?php
/**
 * @file plugins/generic/socialMedia/controllers/grid/MessageQueueGridCellProvider.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class MessageQueueGridCellProvider
 * @ingroup controllers_grid_postingChannels
 *
 * @brief Cell provider for title column of posting channel type grid.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class MessageQueueGridCellProvider extends GridCellProvider {
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
        $message = $row->getData();
        $columnId = $column->getId();

        switch ($columnId) {
            case 'message':
                return [
                    'label' => $message->getValue(),
                    'messageId' => $message->getId()
                ];
                break;
        }
    }
}

?>
