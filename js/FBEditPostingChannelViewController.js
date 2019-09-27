/**
 * @file plugins/generic/socialMedia/js/FBEditPostingChannelViewController.js
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @brief Handles the Facebook posting channel form
 */

$(function(){
    let viewController = Object();
    document.viewController = null;
    document.viewController = viewController;
    viewController.debug = false;

    let componentList = Object({
        "facebookStatusView": null,
        "i18n": null,
        "facebook": null,
        "facebookUsernameView": null,
        "facebookAppIdView": null,
        "facebookPageSelect": null
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

    viewController.getComponents = function() {
        return this._components;
    };

    viewController.addComponent = function(name, component) {
        if (viewController.debug) console.log("Adding: " + name);

        if (this.getComponent(name) === null) {
            this.setComponent(name, component);
        }

        if (this.isFullySetup()) {
            $(this).trigger("viewReady");
        }
    };

    viewController.isFullySetup = function() {
        // Return true when all components are fully loaded

        if (this._components.onlyFacebookNull()) {
            facebook.init();
        }

        if (this.getComponent("facebook") === null) {
            if (viewController.debug) console.log("facebook not setup yet");
            return false;
        }

        return true;
    };

    viewController.getComponent = function(name) {
        return this._components[name];
    };

    viewController.setComponent = function(name, component) {
        this._components[name] = component;
    };

    viewController.getLabel = function(key) {
        let labels = this.getComponent("i18n").labels;

        key = "channelSettings." + key;

        if (!key in labels) {
            return "";
        }

        return labels[key];
    }


    // Callbacks

    viewController.viewReadyCallback = function() {
        document.i18n = {};
        document.viewController.labels = document.i18n.labels;

        // All views are loaded. Check if user is logged into FB
        let savedPageId = $('#pageId').val();

        this.getComponent('facebookStatusView').init();
        this.getComponent('facebookPageSelect').setDBPageId(savedPageId);

        FB.getLoginStatus(function(response) {
            document.viewController.statusChangeCallback(response);
        });
    };

    viewController.fetchAccountCallback = function(response) {
        let accounts = response.data;
        let requiredTasks = ["CREATE_CONTENT", "MANAGE"];
        var didEnable = false;
        let select = document.viewController.getComponent("facebookPageSelect");

        select.resetOptions();

        pageIterate: for (page of accounts) {
            // Skip pages with insufficient privileges
            for (role of requiredTasks) {
                if (page.tasks.indexOf(role) === -1) {
                    continue pageIterate;
                }
            }

            // Append option to select
            select.enable();
            select.addOption(page.name, page.id);
            didEnable = true;
        }

        if (!didEnable) {
            document.viewController.getComponent("facebookStatusView").updateStatusMessage("no_page_to_choose_from");
        }
    };

    viewController.fetchMeCallback = function(response) {
        let username = response.name;

        if (viewController.debug) console.log("/me/ response");
        if (viewController.debug) console.log(response);

        document.viewController.getComponent("facebookUsernameView").setUsername(username);

        FB.api('/me/accounts', function(response){
            if (viewController.debug) console.log("/me/accounts/ response");
            if (viewController.debug) console.log(response);
            document.viewController.fetchAccountCallback(response);
        });
    };

    viewController.loginCallback = function() {
        if (viewController.debug) { console.log("loginCallback"); }
        FB.getLoginStatus(function(response) {
            document.viewController.statusChangeCallback(response);
        });
    };

    viewController.statusChangeCallback = function(response) {
        if (viewController.debug) { console.log("statusChangeCallback"); }
        if (viewController.debug) { console.log(`Status: ${response.status}`); }
        if (response.status === "connected") {
            document.viewController.getComponent("facebookStatusView").addFacebookButton();
            document.viewController.getComponent("facebookStatusView").updateStatusMessage("connected");
            FB.api('/me', this.fetchMeCallback);
        } else if (response.status === "not_authorized") {
            document.viewController.getComponent("facebookStatusView").updateStatusMessage("not_authorized");
            document.viewController.getComponent("facebookStatusView").addFacebookButton();
        } else if (response.status === "authorization_expired") {
            document.viewController.getComponent("facebookStatusView").updateStatusMessage("authorization_expired");
        } else if (response.status === "unknown") {
            document.viewController.getComponent("facebookStatusView").updateStatusMessage("unknown");
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
    // Facebook Page Select
    //

    let fbPageSelect = $('#fbPageId');

    fbPageSelect.addOption = function(name, pageId) {
        if ($(this).children().length == 0) {
            this.append($('<option/>', {value : ""}).text(
                "Please choose an option"
            ));
        }

        this.append($('<option/>', { value : pageId}).text(name));

        if (pageId === this.dbPageId) {
            this.val(pageId);
        }
    };

    fbPageSelect.resetOptions = function() {
        $(this).empty();
    }

    fbPageSelect.enable = function() {
        this.removeAttr('disabled');
    };

    fbPageSelect.disable = function() {
        this.attr('disabled', 'disabled');
    };

    fbPageSelect.setDBPageId = function(id) {
        this.dbPageId = id;
    };

    document.viewController.addComponent("facebookPageSelect", fbPageSelect);

    //
    // Facebook Username
    //

    let fbUsernameView = $('input[name=fbUsername');

    fbUsernameView.setUsername = function(username) {
        this.val(username);
    };

    viewController.addComponent("facebookUsernameView", fbUsernameView);

    //
    // Facebook App
    //

    let fbAppIdView = $('input[name=fbAppId]');

    fbAppIdView.getAppId = function() {
        return this.val();
    }

    document.viewController.addComponent("facebookAppIdView", fbAppIdView);

    //
    // Facebook Status View
    //

    let fbStatusView = $("#facebookStatus");

    fbStatusView.init = function() {
        this.addFacebookButton();
    };

    fbStatusView.updateStatusMessage = function(state) {
        var paragraph1 = "&nbsp;";

        if (state === "connected") {
            this.setStatusIndicator("success");
            this.find("#statusIndicator").addClass("messageSuccess");

            paragraph1 = document.viewController.getLabel("connectionSuccess");
        }

        if (state === "app_id_empty") {
            console.log("missing")
            this.setStatusIndicator("warning");
            paragraph1 = document.viewController.getLabel("appIdMissing");
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

        if (state === "no_page_to_choose_from") {
            this.setStatusIndicator("warning");
            paragraph1 = document.viewController.getLabel("noPageToChooseFrom");
        }

        if (state === "unknown") {
            this.setStatusIndicator("error");
            paragraph1 = document.viewController.getLabel("notLoggedInYet");

            if (document.viewController.getComponent("facebook").wasConnected) {
                paragraph1 = document.viewController.getLabel("noLongerLoggedIn");
            }
        }

        this.find("#statusMessage").html(paragraph1);
    };

    fbStatusView.statusIndicator = function() {
        return this.find("#statusIndicator");
    }

    fbStatusView.setStatusIndicator = function(state) {
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

    fbStatusView.addFacebookButton = function() {
        let html = `<fb:login-button id="fbLoginButton" scope="manage_pages,publish_pages" data-auto-logout-link="true" onlogin="document.viewController.loginCallback();"></fb:login-button>`;

        $("#fbButtonContainer").html(html);

        FB.XFBML.parse(document.getElementById('messageQueue'));
    };

    fbStatusView.hideStatusMessage = function() {
        $(this).find("#statusMessage").html("&nbsp;");
    };

    document.viewController.addComponent("facebookStatusView", fbStatusView);

    //
    // FACEBOOK
    //

    let facebook = Object();

    facebook.wasConnected = false;

    facebook.init = function() {
        let appId = document.viewController.getComponent("facebookAppIdView").getAppId();

        if (appId === "") {
            document.viewController.getComponent("facebookStatusView").updateStatusMessage("app_id_empty");
            return;
        }

        if (typeof FB === 'undefined'){
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