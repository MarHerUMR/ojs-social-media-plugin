# OJS Social Media Plugin

The social media plugin enables enriched OJS links, adds links to social media in the sidebar and allows autoposting of new issues. Other plugins can extend the plugin.

## Features

The features of the Social Media Plugin are:

1. Twitter Cards and Open Graph protocol support
2. Social media sidebar block
3. Autoposting
4. Extensibility

### Twitter Cards and Open Graph protocol

Out of the box the plugin can provide meta tags, used by [Twitter Cards][twitter-cards] and the [Open Graph protocol][open-graph] (used by Facebook).

When posting a link to Twitter or Facebook the post will be automatically enriched by information like the title of the page/article, a short description etc.

Tweet without the _Social Media Plugin_:

![A preview of a Tweet without the enriched content][twitter-without-meta-tags-preview]

Tweet with the _Social Media Plugin_:

![A preview of a Tweet with the enriched content][twitter-meta-tags-preview]

### Social media sidebar block

You can easily provide links to the Twitter account and Facebook presence in the sidebar.

![An example of a Social media sidebar block][social-media-sidebar-block-example]

### Autoposting

The plugin provides the possibility to post automatically to Facebook and Twitter when a new journal issue is published. It will generate a post for the new issue and all its articles. Tweets will get posted in a user configured interval. For Facebook the plugin will add the posts as _scheduled posts_ to your Facebook page. The Facebook posts will be scheduled with the user configured interval.

### Extensibility

The functionality of the _Social Media Plugin_ can be extended by other plugins. The Facebook and Twitter functionality are provided by so called _social media platform plugins_. Developers have the opportunity to make their own plugins and hook into the _Social Media Plugin_ to e.g. provide their own meta tags, sidebar block entries or autoposting.

## System Requirements

The plugin has been developed with/for Open Journal Systems 3.1.1.4.

PHP cURL has to be enabled in order to use autoposting.

## Installation

### Via git

- cd into your OJS root folder
- `cd plugins/generic`
- `git clone https://github.com/MarHerUMR/ojs-social-media-plugin socialMedia`
- `cd ../..`
- `php tools/upgrade.php upgrade`

### Via download

- Download the plugin [here][plugin-download-link]
- Unzip the plugin
- Move the `socialMedia` folder to the `OJS/plugins/generic` folder
- In the root folder of your OJS installation run `php tools/upgrade.php upgrade`


## Configuration

### Social media meta tags (Twitter Cards and Open Graph Protocol)

The _Social Media_ settings are a part of the _Website_ settings in OJS. In the settings tab you can enable the meta tags for the _Open Graph Protocol_ and _Twitter Cards_. Here you can specify a Twitter account the journals _Twitter Cards_ should be attributed to.

![The settings tab][meta-tag-settings]

### Social media sidebar block

The _Social Media_ settings tab also provides the details for the sidebar block plugin. When you want to link to the journals Facebook page or Twitter account you can add them here.

The sidebar plugin itself is enabled/disabled in the Plugins tab like the other _Block Plugins_.

![The plugins tab with the Social Media Sidebar Block plugin][block-plugin]


### Autoposting

#### Scheduled Tasks

The autposting functionality of the _Social Media Plugin_ requires the execution of _scheduled tasks_. You or your administrator has to enable scheduled tasks in your `config.inc.php`.

```
; Enable support for running scheduled tasks
; Set this to On if you have set up the scheduled tasks script to
; execute periodically
scheduled_tasks = On
```

OJS provides two ways to run _scheduled tasks_.

1. cron
2. acron plugin

##### cron

> "The software utility cron is a time-based job scheduler in Unix-like computer operating systems." ([Source](https://en.wikipedia.org/wiki/Cron))

The use of _cron_ is the recommended way for automating the posting. With _cron_ you can execute commands in defined intervals. In order to run the _scheduled tasks_ you need to call the `runScheduledTasks.php` script in the `tools` folder of your OJS installation and pass the `scheduledTasks.xml` of the _Social Media Plugin_ as the parameter. Please note that you have to use absolute paths.

Here is an example (running the script every minute):

```
* * * * * AbsolutePathToYourPHP/php AbsolutePathToYourOJS/tools/runScheduledTasks.php AbsolutePathToYourOJS/plugins/generic/socialMedia/scheduledTasks.xml
```

Adjust the timing according to your needs. If you want to post in small intervals the script should at least once in that interval. If you want to post in larger intervals (like once a day or once a week) it is recommended to call the script less often because OJS creates a log file every time the script is run.

##### acron Plugin

The _acron plugin_ is a possibility to automate tasks in case you do not have access to _cron_ on your host. The plugin will look for _scheduled tasks_ (and run them when they are due) when someone accesses your OJS installation. This also means that the intervals you set up can not be guaranteed. When you set an interval to post once per hour but your OJS does not get accessed for an hour the next post will be done later.

In order to use the _acron plugin_ the OJS administrator has to reload the scheduled tasks after the installation of the _Social Media Plugin_. The reload of the scheduled tasks is done from the plugin settings page.

![Plugin settings with acron Reload Scheduled Tasks link][acron-reload-scheduled-tasks]

#### Posting channels

The autoposting is organized in posting channels. Every posting channel is associated to a Twitter account / Facebook page, has its own posting frequency and message queue.

![The settings tab with two posting channels][posting-channels]

In the message queue you can see the messages that will be posted next.

![The queue of a Twitter posting channel][twitter-message-queue]

In the archive you can see the messages that have been already sent.

![The archive of a Twitter posting channel][twitter-message-archive]

#### Twitter developer app

In order to use the autoposting on Twitter you need four keys to authenticate with Twitter. You need a _Consumer Key_, a _Consumer Secret_, an _Access Token_ and an _Access Token Secret_.

To get those four keys a [Twitter developer app][twitter-developer-app] is required. There are two ways you can get access to a Twitter developer app. Either the operator of your OJS instance already has a _Twitter developer app_ you can use or you have to make your own.

#### Using an existing Twitter developer app

When it is possible to use an existing _Twitter developer app_ you will have to grant this application the rights to post to your Twitter account on your behalf.

From the owner of the _Twitter developer app_ you have to get the _Consumer Key_ and the _Consumer Secret_ (on the Twitter developer page they are called _Consumer API keys_). You will use those keys in the authorization process.

![Twitter API keys][twitter-api-keys]

In order to complete the authorization you have to give the owner of the _Twitter developer app_ your _callback URL_. You can find the URL at the settings page of the posting channel. When the owner of the _Twitter developer app_ has added your _callback URL_ you can start the authorization process.

##### Step 1: Save the Consumer API keys

![The settings page of a Twitter posting channel with example consumer key and consumer secret.][twitter-posting-channel-api-keys]

##### Step 2: Send the callback URL to the application owner

![The settings page of a Twitter posting channel with example consumer key and consumer secret.][twitter-posting-channel-callback-url]

##### Step 3: Start the authorization process

When you start the process you will be redirected to Twitter. If you are not already logged on you will be asked for your Twitter username and password.

![The settings page of a Twitter posting channel with the link to start the Twitter developer app authorization.][twitter-posting-channel-start-authorization]

##### Step 4: Authorize the application

When you have granted the access to your account you will be redirected back to your OJS. On the page you will see the Twitter username, the _Access Token_ and the _Access Token Secret_.

![The Twitter page asking to authorize the Twitter developer app to access your account.][twitter-authorize-developer-app]

##### Step 5: Save the access token and access token secret

After successful authorization you will be redirected a page with Twitter user name of the account you authorized, the _Access Token_ and the _Access Token Secret_.

![After authorizing you will be redirected to OJS and the Access Token and Access Token Secret will be displayed.][twitter-access-token-success]

Take the _Access Token_ and the _Access Token Secret_ and save them at the settings page of your posting channel:

![Save the Access Token and Access Token Secret to your posting channel settings.][twitter-add-access-token]

#### Using your own Twitter developer app

When you do not have access to a _Twitter developer app_ you can use your own. If you do not already have a _Twitter developer app_ you must have an approved [developer account][twitter-developer-account].

##### Callback URL

In your _Twitter developer app_ settings you have to add the callback URL of every Twitter posting channel.

![The settings page of a Twitter posting channel with example consumer key and consumer secret.][twitter-posting-channel-callback-url]

You can find the callback URL of a Twitter posting channel at the bottom of  the posting channel settings form.

![The settings page of a Twitter posting channel with example consumer key and consumer secret.][twitter-app-settings-callback-url]

##### Credentials

You can find the credentials needed in your Twitter developer apps _Details_ under _Keys and tokens_. Take the _Cosumer API Keys_, the _access token_ and _access token secret_ and save them in your Twitter posting channel.

![Under 'Keys and tokens' you get the API credentials you need for the Twitter posting channel.][twitter-api-keys]

Please note that if you use the _access token_ and _access token secret_ from the _Keys and tokens_ page the autoposting will post to the Twitter developer account you created the Twitter developer app with. In order to post to another account you have to follow steps of an existing _Twitter developer app_ above.

##### Permissions

In order to post with your _Twitter developer app_ please make sure that the _Access permission_ is set to Read and write.

#### Facebook app

In order to use the autoposting feature for Facebook you need to have a _Facebook app_. In contrast to Twitter not every posting will be done individually. All the posts will be transmitted at once as _scheduled posts_.

With the OJS Social Media Plugin you can post to Facebook pages only. The _scheduled posts_ will be listed in the settings of your Facebook page.

In order to be able to schedule posts on Facebook you need the identifier (id) of the _Facebook app_ and you need to the grant _Facebook app_ access to the Facebook page.

#### Privacy and Facebook

When you want to use the Facebook functionality you have to explicitly agree to the use of third party cookies, the [Facebook Terms of Service][fb-tos], [Facebook Cookie Policy][fb-cookie-policy], [Facebook Data Policy][fb-data-policy] and [Facebook Advertising Policies][fb-ad-policy].

In order to agree you have to go to your _Personal Platform Settings_. There you can set the checkmark to agree and after saving you will be able to make the necessary settings for the posting channel.

#### Save Facebook application id

Open the posting channel settings and enter the id of the _Facebook app_. Save the settings and open the settings form again.

#### Log into Facebook

![The Facebook posting channel form with a message that the Facebook app ID is missing.][fb-enter-app-id]

After opening the form again you will be asked to log into Facebook to continue.

![The Facebook posting channel form with a message asking you to log in.][fb-login]

When you click on the Facebook Log In button you will get to the Facebook page asking for you username and password. The page will state the name of the Facebook App the login will be used for (the _Facebook app_ does not get your login credentials).

![The Facebook login screen with the name of the Facebook app.][fb-login-screen]

After that Facebook asks you if you really want to continue with this account. The privacy policy of the _Facebook app_ is linked at the bottom of the page.

![The Facebook login screen asking you if you want to continue the process with the name connected with the account you logged in with.][fb-login-step-1]

After confirming the account to use you will be presented with all the Facebook pages you are managing. You can decided what pages you want to use the _Facebook app_ with.

![The Facebook login screen asking you which of your Facebook pages should be accessible for the Facebook app.][fb-login-step-2]

After choosing the Facebook pages you want to be able to post to you will get asked what permissions you want to give to the _Facebook app_. In order to schedule posts you have to grant the _Manage your Pages_ and the _Publish as Pages you manage_ permissions.

![The Facebook login screen asking what permission you want to grant for the Facebook app.][fb-login-step-3]

Click on done and you are done. The Facebook status should tell you that you are connected successfully and the _Facebook User Name_ field should contain the username you used to log into (when nothing has happened please close and open the settings form).

You can now select the Facebook page the channel will post to. After saving you are ready to post.

![The Facebook posting channel form with an opened drop down menu where you can choose the Facebook page the posting channel should post to.][fb-page-select]

#### Schedule message

To actually schedule the messages in the posting channels message queue you have to open the _Message queue_. On the message queue view you can see what messages have not been scheduled yet and the messages that are scheduled but not public yet. To schedule the messages click on _Schedule Messages_. The messages will get moved from the queue to the _Scheduled Messages_ list when they have been scheduled successfully.

So after publication of a new issue you have to visit the message queue(s) of your Facebook posting channel(s) once to schedule the messages.

![The message queue of a Facebook posting channel.][fb-message-queue]

[comment]: Links

[fb-ad-policy]: https://www.facebook.com/policies/ads/
[fb-cookie-policy]: https://www.facebook.com/policies/cookies/
[fb-data-policy]: https://www.facebook.com/about/privacy/
[fb-tos]: https://www.facebook.com/terms.php
[open-graph]: http://ogp.me/
[twitter-cards]: https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/abouts-cards
[twitter-developer-account]: https://developer.twitter.com/en/docs/basics/developer-portal/overview
[twitter-developer-app]: https://developer.twitter.com/en/docs/basics/apps/overview

[comment]: Screenshots

[acron-reload-scheduled-tasks]: images/acron-reload-scheduled-tasks.png
[block-plugin]: images/block-plugin.png
[fb-enter-app-id]: images/fb-enter-app-id.png
[fb-login-screen]: images/fb-login-screen.png
[fb-login-step-1]: images/fb-login-step-1.png
[fb-login-step-2]: images/fb-login-step-2.png
[fb-login-step-3]: images/fb-login-step-3.png
[fb-login]: images/fb-login.png
[fb-message-queue]: images/fb-message-queue.png
[fb-page-select]: images/fb-page-select.png
[meta-tag-settings]: images/meta-tag-settings.png
[posting-channels]: images/posting-channels.png
[social-media-sidebar-block-example]: images/social-media-sidebar-block-example.png
[twitter-access-token-success]: images/twitter-access-token-success.png
[twitter-add-access-token]: images/twitter-add-access-token.png
[twitter-api-keys]: images/twitter-api-keys.png
[twitter-app-settings-callback-url]: images/twitter-app-settings-callback-url.png
[twitter-authorize-developer-app]: images/twitter-authorize-developer-app.png
[twitter-message-archive]: images/twitter-message-archive.png
[twitter-message-queue]: images/twitter-message-queue.png
[twitter-meta-tags-preview]: images/twitter-meta-tags-preview.png
[twitter-posting-channel-api-keys]: images/twitter-posting-channel-api-keys.png
[twitter-posting-channel-callback-url]: images/twitter-posting-channel-callback-url.png
[twitter-posting-channel-start-authorization]: images/twitter-posting-channel-start-authorization.png
[twitter-without-meta-tags-preview]: images/twitter-without-meta-tags-preview.png
