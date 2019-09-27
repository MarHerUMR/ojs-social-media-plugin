/**
 * @file plugins/generic/socialMedia/js/FBMessageQueueViewController.js
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @brief Handles the Facebook message queue interface
 */

$(function(){
    let viewController = Object();
    document.viewController = viewController;

    let componentList = Object({
        "facebookStatusView": null,
        "i18n": null,
        "scheduleGrid": null,
        "queueGrid": null,
        "facebook": null
    });

    componentList.onlyFacebookNull = function() {
        /** Return true when only 'facebook' is null */
        if (this['facebook'] !== null) {
            return false;
        }

        keys = Object.keys(this);

        for (var i = 0; i < keys.length; i++) {
            if (keys[i] != "facebook" && this[keys[i]] === null) {
                return false;
            }
        }

        return true;
    }

    viewController._components = componentList;

    $(viewController).on("viewReady", function() {
        document.viewController.viewReadyCallback();
    });

    viewController._scheduledPosts = null;
    viewController.access_token = null;
    viewController.frequency = null;

    viewController.debug = false;

    viewController.getComponents = function() {
        return this._components;
    };

    viewController.getComponent = function(name) {
        return this._components[name];
    };

    viewController.setComponent = function(name, component) {
        this._components[name] = component;
    };

    viewController.getScheduledPosts = function() {
        return this._scheduledPosts;
    };

    viewController.setScheduledPosts = function(posts) {
        this._scheduledPosts = posts;
        this.updateScheduleGrid();
    };

    viewController.isFullySetup = function() {
        let debug = false;
        // Return true when all components are fully loaded
        if (this.getComponent("scheduleGrid") === null) {
            if ($('#messageQueueScheduleGridContainer:first-child').hasClass("pkp_loading")) {
                if (viewController.debug) console.log("scheduleGrid not setup yet");
                return false;
            } else {
                if (viewController.debug) console.log("scheduleGrid loaded so initialize")
                $("#messageQueueScheduleGridContainer").init();
            }
        }

        if (this._components["queueGrid"] === null) {
            if ($('#messageQueueGridContainer:first-child').hasClass("pkp_loading")) {
                if (viewController.debug) console.log("queueGrid not setup yet");
                return false;
            } else {
                if (viewController.debug) console.log("queueGrid loaded so initialize")
                $("#messageQueueGridContainer").init();
            }
        }

        if (this._components.onlyFacebookNull()) {
            facebook.init();
        }

        if (this.getComponent("facebook") === null) {
            if (viewController.debug) console.log("facebook not setup yet");
            return false;
        }

        return true;
    };

    viewController.getLabel = function(key) {
        key = "messageQueue." + key;
        if (!key in this.getComponent('i18n').labels) {
            return "";
        }

        return this.getComponent('i18n').labels[key];
    }

    viewController.addComponent = function(name, component) {
        if (viewController.debug) console.log("Adding: " + name);

        if (this.getComponent(name) === null) {
            this.setComponent(name, component);
        }

        if (this.isFullySetup()) {
            $(this).trigger("viewReady");
        }
    };

    viewController.checkFacebookLoginState = function() {
        FB.getLoginStatus(function(response) {
            this.statusChangeCallback(response);
        });
    };

    viewController.fetchAccounts = function() {
        FB.api('/me/accounts', this.fetchAccountCallback);
    };

    viewController.fetchPageLink = function() {
        FB.api(
            `/${this.pageId}/?fields=link`,
            "GET",
            {
                "access_token": this.access_token
            },
            this.fetchPageLinkCallback
        );
    };

    viewController.fetchScheduledPosts = function() {
        if (viewController.debug) console.log("fetchScheduledPosts");
        if (viewController.debug) console.log(`pageId ${document.viewController.pageId}`);
        if (viewController.debug) console.log(`access_token ${this.access_token}`);

        FB.api(
            `/${this.pageId}/scheduled_posts`,
            "GET",
            {
                "access_token": this.access_token
            },
            viewController.fetchScheduledPostsCallback
        );
    };

    viewController.loadMessagesToSchedule = function() {
        var messages = this.getComponent("queueGrid").getMessages();

        for (var i = 0; i < messages.length; i++) {
            if (i === 0) {
                messages[i].pubDate = this.getMessageTimestamp();
            } else {
                let prevMessage = messages[i - 1];
                messages[i].pubDate = this.getMessageTimestamp(messages[i - 1].pubDate);
            }
        }

        this.messagesToSchedule = messages;
    };

    viewController.getMessageTimestamp = function(offset) {
        let frequency = this.frequency;
        let amount = parseInt(frequency.slice(0, -1));
        let unit = frequency.slice(-1);


        if (unit == "M" && offset === undefined) {
            // Facebook expects at least 10 minutes from now()
            if (amount < 10) {
                interval = 10 * 60000;
            } else {
                interval = amount * 60000;
            }
        } else if (unit == "M") {
            interval = amount * 60000;
        }

        if (unit == "H") {
            interval = amount * 3600000;
        }

        if (unit === "D") {
            interval = amount;
        }

        if (offset === undefined) {
            var returnDate = new Date();
        } else {
            var returnDate = new Date(offset * 1000);
        }

        if (unit == "D") {
            milliseconds = returnDate.setDate(returnDate.getDate() + interval);
            return Math.floor(milliseconds / 1000);
        } else {
            milliseconds = returnDate.setTime(returnDate.getTime() + interval);
            return Math.floor(milliseconds / 1000);
        }
    };

    viewController.saveMessageAsScheduled = function(message) {
        if (this.debug){
            console.log("saveMessageAsScheduled");
            console.log(message);
        }

        let form = $('#messageQueue');
        let handlerURL = form.attr('action');

        // Add id and pubDate to form so they can be saved
        form.append(`<input type="hidden" id="messageId" name="messageId" value="${message.id}">`);
        form.append(`<input type="hidden" id="datePosted" name="datePosted" value="${message.pubDate}">`);

        form.submit();

        // Remove them again so they do not get saved again
        $('#messageId').remove();
        $('#datePosted').remove();

        this.getComponent('queueGrid').removeMessageRow(message.id);
    };

    viewController.scheduleMessages = function() {
        this.loadMessagesToSchedule();

        // The schedule message callback will keep scheduling until there is no
        // message left to schedule
        this.scheduleMessage();
    };

    viewController.scheduleMessage = function() {
        if (this.messagesToSchedule.length > 0) {
            let message = this.messagesToSchedule.pop();

            if (this.debug) console.log(message);

            this.messagesToSchedule.push(message);

            FB.api(
                `${document.viewController.pageId}/feed`,
                "POST",
                {
                    "message": message.content,
                    "published": "false",
                    "scheduled_publish_time": String(message.pubDate),
                    "access_token": viewController.access_token
                },
                viewController.scheduleMessageCallback
            );
        }
    };

    viewController.updateScheduleGrid = function() {
        let posts = this.getScheduledPosts();
        let grid = document.viewController.getComponent("scheduleGrid");

        // When the scheduled posts are fetched successfully for the 
        // first time add the button for rescheduling
        let isGridEmpty = grid.isEmpty();

        if (isGridEmpty) {
            document.viewController.fetchPageLink();
        }

        if (posts.length === 0) {
            grid.emptyTbody();
        }

        grid.addPosts(posts);
    };

    // Callbacks

    viewController.allMessagesScheduledCallback = function() {
        console.log("DONE");
        this.fetchScheduledPosts();
    }

    viewController.fetchAccountCallback = function(response) {
        if (viewController.debug) console.log("fetchAccountCallback");
        let accounts = response.data;

        function hasNeededPermissions(permissions) {
            let requiredTasks = ["CREATE_CONTENT", "MANAGE"];

            requiredTasks.forEach(function(task) {
                if (permissions.indexOf(task) === -1) {
                    return false;
                }
            });

            return true;
        };


        accounts.forEach(function(account) {
            if (viewController.debug) console.log(`PageId: ${document.viewController.pageId}`);
            if (viewController.debug) console.log(`account.id: ${account.id}`);

            if (account.id == document.viewController.pageId && hasNeededPermissions(account.tasks)) {
                document.viewController.access_token = account.access_token;
            }
        });

        // Check if the needed token could be obtained.
        if (document.viewController.access_token === null) {
            document.viewController.getComponent("scheduleGrid").facebookConnectionFailed(
                "You don't have the required permissions to get the scheduled post of this site."
            );

            document.viewController.getComponent("facebookStatusView").updateStatusMessage("insufficient_rights");

            document.viewController.getComponent("queueGrid").disableScheduleButton();
        } else {
            // Fetch scheduled post if it was obtained
            document.viewController.fetchScheduledPosts();

            // Enable the button to schedule new messages
            document.viewController.getComponent("queueGrid").enableScheduleButton(document.viewController.scheduleMessagesCallback);
        }
    };

    viewController.fetchPageLinkCallback = function(response) {
        if (viewController.debug) console.log("fetchPageLinkCallback");
        if (viewController.debug) console.log(response);

        let link = response["link"];
        document.viewController.getComponent("scheduleGrid").addRescheduleButton(link);
    };

    viewController.fetchScheduledPostsCallback = function(response) {
        let posts = [];
        if (viewController.debug) console.log("fetchScheduledPostsCallback");
        if (viewController.debug) console.log(response);

        response.data.forEach(function(element) {
            posts.push(element)
        });

        document.viewController.setScheduledPosts(posts);

        document.viewController.getComponent('scheduleGrid').emptyTbody();

        document.viewController.updateScheduleGrid();
    };

    viewController.loginCallback = function() {
        if (viewController.debug) { console.log("loginCallback"); }
        FB.getLoginStatus(function(response) {
            document.viewController.statusChangeCallback(response);
        });
    };

    viewController.viewReadyCallback = function() {
        // All views are loaded. Check if user is logged into FB
        if (viewController.debug) { console.log("viewReadyCallback"); }

        this.pageId = $("#messageQueue").find("#pageId").val();

        if (this.pageId == "") throw "PAGE ID NOT SET";

        this.frequency = $("#messageQueue").find("#frequency").val();

        this.getComponent('facebookStatusView').init();

        FB.getLoginStatus(function(response) {
            document.viewController.statusChangeCallback(response);
        });
    };

    viewController.getAppId = function () {
        if (this.appId === undefined) {
            this.appId = $("#messageQueue").find("#appId").val();

            $('#appId').remove();

            if (this.appId == "" || this.appId == undefined) throw "APP ID NOT SET";
        }

        return this.appId;
    };

    viewController.scheduleMessageCallback = function(response) {
        if (viewController.debug) { console.log("scheduleMessageCallback"); }
        if (viewController.debug) { console.log(response); }

        if (response.hasOwnProperty("error") === true) {
            // Handle error
            let error = response.error;

            // Log the errror and response for details
            console.log(`Error ${error.message}`);
            console.log(response);

            // Show the error in the interface
            facebookStatusView = document.viewController.getComponent('facebookStatusView');
            facebookStatusView.updateStatusMessage("scheduleMessageError", error.message);
        } else if (response.hasOwnProperty("id") === true) {
            let message = document.viewController.messagesToSchedule.pop();

            document.viewController.saveMessageAsScheduled(message);

            // If there are more messages to schedule, schedule them.
            if (document.viewController.messagesToSchedule.length > 0) {
                document.viewController.scheduleMessage();
            } else {
                document.viewController.allMessagesScheduledCallback();
            }
        }
    };

    viewController.scheduleMessagesCallback = function() {
        document.viewController.getComponent("queueGrid").disableScheduleButton();
        document.viewController.scheduleMessages();
    };

    viewController.statusChangeCallback = function(response) {
        if (viewController.debug) { console.log("statusChangeCallback"); }
        if (viewController.debug) { console.log(response); }

        if (response.status === "connected") {
            if (viewController.debug) { console.log("connected"); }
            // Logged into our app and Facebook.
            document.viewController.getComponent("facebook").wasConnected = true;
            document.viewController.getComponent("facebookStatusView").addFacebookButton();
            document.viewController.getComponent("facebookStatusView").updateStatusMessage("connected");

            if (document.viewController.access_token === null) {
                // We don't have the access token yet. So fetch it
                document.viewController.fetchAccounts();
            } else {
                // The token was already stored. Fetch posts.
                document.viewController.fetchScheduledPosts();

                // Enable the button to schedule new messages
                document.viewController.getComponent("queueGrid").enableScheduleButton(document.viewController.scheduleMessagesCallback);
            }
        } else if (response.status === "not_authorized") {
            // The person is not logged into your app or we are unable to tell.
            document.viewController.getComponent("queueGrid").disableScheduleButton();
            document.viewController.getComponent("scheduleGrid").removeRescheduleButton();

            document.viewController.getComponent("facebookStatusView").updateStatusMessage("not_authorized");
            document.viewController.getComponent("facebookStatusView").addFacebookButton();

        } else if (response.status === "authorization_expired") {
            document.viewController.getComponent("facebookStatusView").updateStatusMessage("authorization_expired");
            document.viewController.getComponent("scheduleGrid").facebookConnectionFailed(
                "The access you granted has expired. Please log in again to renew the access."
            );

            document.viewController.getComponent("queueGrid").disableScheduleButton();
        } else if (response.status === "unknown") {
            document.viewController.getComponent("facebookStatusView").updateStatusMessage("unknown");
            document.viewController.getComponent("queueGrid").disableScheduleButton();
            document.viewController.getComponent("scheduleGrid").removeRescheduleButton();
        }
    };

    //
    // i18n
    //

    let i18n = Object();

    i18n.loadCallback = function (data) {
        i18n.labels = JSON.parse(data);

        document.viewController.addComponent("i18n", i18n);
    };

    jQuery.ajax({
        url: "/$$$call$$$/plugins/generic/social-media/controllers/js-internationalization/index",
        success: i18n.loadCallback
    });

    //
    // Facebook status view
    //

    let facebookStatusView = $("#facebookStatus");

    facebookStatusView.init = function() {
        this.addFacebookButton();
    };

    facebookStatusView.updateStatusMessage = function(state, details) {
        var paragraph1 = "&nbsp;";

        if (state === "connected") {
            this.setStatusIndicator("success");
            this.find("#statusIndicator").addClass("messageSuccess");

            paragraph1 = document.viewController.getLabel("connectionSuccess");
        }

        if (state === "not_authorized") {
            this.setStatusIndicator("warning");

            paragraph1 = document.viewController.getLabel("notAuthorized");
        }

        if (state === "authorization_expired") {
            this.setStatusIndicator("warning");

            paragraph1 = document.viewController.getLabel("authorizationExpired");
        }

        if (state === "insufficient_rights") {
            this.setStatusIndicator("warning");

            paragraph1 = document.viewController.getLabel("insufficientRights");
        }

        if (state === "unknown") {
            this.setStatusIndicator("error");

            paragraph1 = document.viewController.getLabel("notLoggedIn");

            if (document.viewController.getComponent("facebook").wasConnected) {
                paragraph1 = document.viewController.getLabel("noLongerLoggedIn");
            }
        }

        if (state === "scheduleMessageError") {
            this.setStatusIndicator("error");

            paragraph1 = document.viewController.getLabel("scheduleMessageError");

            paragraph1 += ` <code>${details}</code>`;
        }

        this.find("#statusMessage").html(paragraph1);
    };

    facebookStatusView.statusIndicator = function() {
        return this.find("#statusIndicator");
    }

    facebookStatusView.setStatusIndicator = function(state) {
        let indicator = this.statusIndicator();

        indicator.removeClass("messageSuccess");
        indicator.removeClass("messageWarning");
        indicator.removeClass("messageError");

        if (state === "success") {
            indicator.addClass("messageSuccess");
        }

        if (state === "warning") {
            indicator.addClass("messageWarning");
        }

        if (state === "error") {
            indicator.addClass("messageError");
        }
    }

    facebookStatusView.addFacebookButton = function() {
        let html = `
            <fb:login-button id="fbLoginButton" scope="manage_pages,publish_pages" data-auto-logout-link="true" onlogin="document.viewController.loginCallback();"></fb:login-button>
        `;

        $("#fbButtonContainer").html(html);

        FB.XFBML.parse(document.getElementById('messageQueue'));
    };

    facebookStatusView.hideStatusMessage = function() {
        $(this).find("#statusMessage").html("&nbsp;");
    };

    document.viewController.addComponent("facebookStatusView", facebookStatusView);

    // Schedule grid

    let scheduleGrid = $('#messageQueueScheduleGridContainer');

    scheduleGrid.on("gridInitialized", function(event) {
        scheduleGrid.init();
    });

    scheduleGrid.init = function() {
        // Remove the empty body
        scheduleGrid.find(".empty").hide();
        document.viewController.addComponent("scheduleGrid", scheduleGrid);

        // Add a row
        scheduleGrid.facebookConnectionFailed("");
    };

    scheduleGrid.tbody = function () {
        let tbody = scheduleGrid.find('tbody').not(".empty");

        return tbody;
    };

    scheduleGrid.isEmpty = function() {
        if (this.tbody().find("tr").length === 0) {
            return true;
        }

        return false;
    };

    scheduleGrid.addPost = function(post) {
        let html = `
        <tr class="gridRow">
            <td class="first_column">
                <span id="cell-1-message" class="gridCellContainer"><span class="label">${post.message}</span></span>
            </td>
        </tr>`;

        this.addRow(html);
    };

    scheduleGrid.addPosts = function(posts) {
        posts.forEach(function(post) {
            scheduleGrid.addPost(post);
        });
    }

    scheduleGrid.addRow = function(row) {
        this.find(".empty").hide();
        this.tbody().show();
        this.tbody().append(row);
    };

    scheduleGrid.empty = function() {
        scheduleGrid.find(".empty").show();
        scheduleGrid.tbody().hide();
    };

    scheduleGrid.emptyTbody = function() {
        this.tbody().html("");
        this.empty();
    };

    scheduleGrid.facebookConnectionFailed = function(reason) {
        let row = `
        <tr>
            <td>
                <p>The scheduled posts from Facebook could not be fetched.</p>
                <p>${reason}</p>
            </td>
        </tr>`;

        scheduleGrid.emptyTbody();
        scheduleGrid.addRow(row);
        scheduleGrid.removeRescheduleButton();
    };

    scheduleGrid.rescheduleButton = function(pageURL) {
        let url = `${pageURL}publishing_tools/?section=SCHEDULED_POSTS&sort[0]=scheduled_publish_time_ascending`;
        let html = `
            <ul class="actions">
                <li>
                    <a id="rescheduleButton" class="pkp_controllers_linkAction" href="${url}">Reschedule on Facebook</a>
                </li>
            </ul>
        `;

        return html;
    };

    scheduleGrid.addRescheduleButton = function(pageURL) {
        let button = this.rescheduleButton(pageURL);
        let header = this.find("div").find(".header");

        if (this.find('#rescheduleButton').length === 0) {
            header.append(button);
        }
    };

    scheduleGrid.removeRescheduleButton = function() {
        this.find("#rescheduleButton").parent().parent().remove();
    };

    // Queue grid

    let queueGrid = $("#messageQueueGridContainer");

    queueGrid.on("gridInitialized", function(event) {
        queueGrid.init();
    });

    queueGrid.init = function() {
        document.viewController.addComponent("queueGrid", queueGrid);

        if (!queueGrid.isEmpty()) {
            queueGrid.addScheduleButton();
        }
    };

    queueGrid.nonEmptyTbody = function() {
        let tbody = this.find("tbody").not(".empty");

        return tbody;
    };

    queueGrid.getMessages = function() {
        var posts = [];

        let rows = this.find("tr");

        function Message(id, content) {
            this.id = id;
            this.content = content;
        }

        rows.each(function() {
            let col = $(this).find(".first_column");

            if (col.length === 1) {
                let content = col.find(".label").text().trim();
                let messageId = col.find("[data-message-id]").attr("data-message-id");
                posts.push(new Message(messageId, content));
            }
        });

        return posts;
    };

    queueGrid.removeMessageRow = function (messageId) {
        var message = this.find(`[data-message-id='${messageId}']`);

        if (message.length > 0) {
            $(message[0]).parent().parent().parent().remove();
        }

        if (this.isEmpty()) {
            this.showEmptyBody();
        }
    };

    queueGrid.isEmpty = function() {
        if (this.nonEmptyTbody().find(".gridRow").length === 0) {
            return true;
        }

        return false;
    };

    queueGrid.scheduleButton = function() {
        let html = `
        <ul class="actions">
            <li>
                <a id="scheduleButton" class="pkp_controllers_linkAction" disabled="disabled" href="#">Schedule Messages</a>
            </li>
        </ul>
        `;

        return html;
    };

    queueGrid.addScheduleButton = function() {
        let button = this.scheduleButton();
        let header = this.find("div").find(".header");

        header.append(button);
    };

    queueGrid.enableScheduleButton = function(callback) {
        let scheduleButton = this.find("#scheduleButton");

        if ($._data( scheduleButton[0], 'events' ) === undefined) {
            scheduleButton.on("click.scheduleClick", callback);
        }

        scheduleButton.removeAttr("disabled");
    };

    queueGrid.disableScheduleButton = function() {
        let scheduleButton = this.find("#scheduleButton");

        scheduleButton.off(".scheduleClick");
        scheduleButton.attr("disabled", "disabled");
    };

    queueGrid.showEmptyBody = function() {
        body = this.find(".empty")[0];
        $(body).attr("style", "");
    };

    //
    // FACEBOOK
    //

    let facebook = Object();

    facebook.wasConnected = false;

    facebook.init = function() {
        var appId = ""

        appId = document.viewController.getAppId();

        if (appId === "") {
            document.viewController.getComponent("facebookStatusView").updateStatusMessage("app_id_empty");
            return;
        }

        if(typeof FB === 'undefined'){
            window.fbAsyncInit = function() {
                FB.init({
                    appId      : appId,
                    cookie     : true,
                    xfbml      : true,
                    version    : 'v3.1'
                });

                document.viewController.addComponent("facebook", facebook);
            };

            // Load the SDK asynchronously
            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "https://connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        } else {
            document.viewController.addComponent("facebook", facebook);
        }
    };
});