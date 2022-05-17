<?php
include __DIR__.'/vendor/autoload.php';
//Configure Environment Variables
$strJsonFileContents = file_get_contents("config.json");
// Convert to array
$config = json_decode($strJsonFileContents, true);

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Discord\WebSockets\Event;

$discord = new Discord([
    'token' => $config['token'],
]);

$discord->on('ready', function (Discord $discord) use ($config) {
echo "Bot is ready!", PHP_EOL;

// Listen for messages.
$discord->on(Event::INTERACTION_CREATE, function ( Interaction $interaction, Discord $discord) use ($config) {
    $client = new GuzzleHttp\Client();
    try {
        $res = $client->request('GET', 'https://data.bus-data.dft.gov.uk/api/v1/datafeed/?lineRef=EL1&api_key=' . $config['bus_api_key']);

        //Convert XML to JSON
        $xml = simplexml_load_string($res->getBody());
        $json = json_encode($xml);
        $BusData = json_decode($json,TRUE);
        $lat = $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]["MonitoredVehicleJourney"]["VehicleLocation"]["Latitude"];
        $long = $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]["MonitoredVehicleJourney"]["VehicleLocation"]["Longitude"];

        $embed = new \Discord\Parts\Embed\Embed($discord);
        $embed->setTitle("EL1 Bus Finder");
        $embed->setDescription("Location data for the Edge Hill Bus");
        $embed->setAuthor("Dan Bracey", "https://avatars.githubusercontent.com/u/16801642?v=4", "https://github.com/PenguinNexus");
        $embed->setColor("#0099ff");
        $embed->setURL("https://discord.js.org/");
        $embed->setType("rich");
        $embed->addField([
            'name' => 'Data Valid Until: ',
            'value' => $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["ValidUntil"]
        ]);
        /**$embed->addField([
            'name' => 'Direction: ',
            'value' => $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]["MonitoredVehicleJourney"]["DirectionRef"]
        ]);**/
        $embed->addField([
            'name' => 'Operator Reference: ',
            'value' => $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]["MonitoredVehicleJourney"]["OperatorRef"]
        ]);
        $embed->addField([
            'name' => 'Longitude: ',
            'value' => $long
        ]);
        $embed->addField([
            'name' => 'Latitude: ',
            'value' => $lat
        ]);
        $embed->addField([
            'name' => 'Vehicle Reference: ',
            'value' => $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]["MonitoredVehicleJourney"]["VehicleRef"]
        ]);
        $embed->addField([
            'name' => 'Driver: ',
            'value' => $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]["Extensions"]["VehicleJourney"]["DriverRef"]
        ]);
        $embed->setImage("https://maps.googleapis.com/maps/api/staticmap?center=" . $lat . "," . $long . "&size=500x400&key=" . $config['map_key'] . "&markers=color:blue%7C" .  $lat . "," . $long);

        $interaction->respondWithMessage(MessageBuilder::new()
            ->setContent("EL1 Bus Data")
            ->addEmbed($embed)
        );
    } catch(Exception $exception) {
        $interaction->respondWithMessage(MessageBuilder::new()
            ->setContent("Unable to find Bus! The most common cause of this is that the bus isn't running / has finished for the day, please try again later!")
        );
    }
});
});

$discord->run();