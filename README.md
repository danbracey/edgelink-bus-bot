
# Edge Link Bus Tracker (Discord)

This bus is written in PHP and allows Discord servers to track the Edge Hill Bus (EL1) for [Edge Hill University](https://www.edgehill.ac.uk/).

This tracker uses API data from the Government Bus Open Data Service (BODS) in order to pull data in, and uses Google Maps Cloud API in order to display the tracking map.

## Prerequisites
[Discord Bot Token](https://discord.com/developers/applications)

[Bus Open Data Service API Key](https://data.bus-data.dft.gov.uk/account/)

[Google Cloud / Static Maps API](https://developers.google.com/maps/documentation/maps-static/overview)

[Composer](https://getcomposer.org/)


## Deployment

To install and use, you'll need the following:

Create a new application in the [Discord Developer Portal](https://discord.com/developers/applications), and create a Bot for this application. Copy/Generate the token for this bot, and add it to the config.json file.

Add the Discord Application (Bot) you've created to the server you want to use the bot on. It requires Bot and Application (Slash) Commands scopes. You can add it to your Discord server with the following URL: https://discord.com/api/oauth2/authorize?client_id=<<Replace with Client ID (found in Discord Developer Portal)>>&permissions=0&scope=bot%20applications.commands

Clone this repo, and copy config.example.json to config.json, replacing the values provided with the necessary tokens/ID values.

Make sure PHP & Composer is installed, run `composer install` and run `php index.php` through the CLI. This will work with the PHP CLI only.

Once the bot is running, use the slash command /bus in a channel to view the current location data of the Edge Link Bus
## Authors

- [Dan Bracey](https://github.com/danbracey)


## Acknowledgements

- [Bus Open Data Service](https://www.bus-data.dft.gov.uk/)

