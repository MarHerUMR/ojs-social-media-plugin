<?php
/**
 * @file plugins.generic.socialMedia.plugins.facebook.controllers.grid.FacebookMessageQueueGridHandler.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class FacebookMessageQueueGridHandler
 * @ingroup controllers_grid_postingChannels
 *
 * @brief Handle message queue grid requests.
 */

import('plugins.generic.socialMedia.controllers.grid.MessageQueueGridHandler');

class FacebookMessageQueueGridHandler extends MessageQueueGridHandler {
    /**
     * @copydoc Gridhandler::fetchGrid()
     */
    function fetchScheduleGrid($args, $request) {
        $this->_isFetchForArchive = false;
        $this->setTitle('plugins.generic.socialMedia.form.autoposter.facebookScheduleGrid');
        // Set the no items row text
        $this->setEmptyRowText('plugins.generic.socialMedia.form.autoposter.noMessagesScheduled');
        return $this->fetchGrid($args, $request);
    }
}

?>
