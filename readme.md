# OJS Social Media Plugin

## Features

The features of the Social Media Plugin are:

1. Twitter Cards and Open Graph protocol support
2. Social media sidebar block
3. Autoposting
4. Extensibility

### Twitter Cards and Open Graph protocol

Out of the box the plugin can provide meta tags, used by [Twitter Cards](https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/abouts-cards) and the [Open Graph protocol](http://ogp.me/) (used by Facebook).

When posting a link to Facebook or Twitter the post will automatically enriched by information like the title of the page/article, a short description etc.

### Social media sidebar block

You can easily provide links to the Facebook page and Twitter account in the sidebar.

## Extensebility

This plugin is extensible by other plugins. Via the hook system plugins can add their own meta tags, add entries for the sidebar block or provide autoposting.

# Installation

## Via git

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

## Development

The plugin has been developed by [Markus Hermann](https://github.com/MarHerUMR) at [Middle East – Topics & Arguments](https://www.meta-journal.net/).

