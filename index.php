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
    if($interaction["data"]["name"] === "bus") {
        try {
            $client = new GuzzleHttp\Client();
            $res = $client->request('GET', 'https://data.bus-data.dft.gov.uk/api/v1/datafeed/?lineRef=EL1&api_key=' . $config['bus_api_key']);

            //Convert XML to JSON
            $xml = simplexml_load_string($res->getBody());
            $json = json_encode($xml);
            $BusData = json_decode($json,TRUE);

            //Check the bus is running, and exit if not
            if(! $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]) {
                $interaction->respondWithMessage(MessageBuilder::new()
                    ->setContent("Unable to find Bus! The most common cause of this is that the bus isn't running / has finished for the day, please try again later!")
                );

            } else {
                $lat = $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]["MonitoredVehicleJourney"]["VehicleLocation"]["Latitude"];
                $long = $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]["MonitoredVehicleJourney"]["VehicleLocation"]["Longitude"];
                $timestamp = strtotime($BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["ValidUntil"]);
                $valid_date = date('d-m-Y H:i:s', $timestamp);

                $embed = new \Discord\Parts\Embed\Embed($discord);
                $embed->setTitle("EL1 Bus Finder");
                $embed->setDescription("Location data for the Edge Hill Bus");
                $embed->setAuthor("Dan Bracey", "https://avatars.githubusercontent.com/u/16801642?v=4", "https://github.com/PenguinNexus/edgelink-bus-bot");
                $embed->setColor("#671e75");

                $embed->addField([
                    'name' => 'Data Valid Until: ',
                    'value' => $valid_date
                ]);
                $embed->addField([
                    'name' => 'Operator Reference: ',
                    'value' => $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]["MonitoredVehicleJourney"]["OperatorRef"]
                ]);
                $embed->addField([
                    'name' => 'Location (Lat, Long): ',
                    'value' => "(" . $lat . ", " . $long . ")"
                ]);
                $embed->addField([
                    'name' => 'Vehicle Reference: ',
                    'value' => $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]["MonitoredVehicleJourney"]["VehicleRef"]
                ]);
                $embed->addField([
                    'name' => 'Driver: ',
                    'value' => $BusData['ServiceDelivery']["VehicleMonitoringDelivery"]["VehicleActivity"]["Extensions"]["VehicleJourney"]["DriverRef"]
                ]);
                /**Old Map Route **/
                //$embed->setImage("https://maps.googleapis.com/maps/api/staticmap?center=53.562528,-2.875634&size=500x400&key=" . $config['map_key'] . "&markers=color:blue%7C" .  $lat . "," . $long . "&zoom=14&path=color:0x0000ff|weight:3|53.567277,%20-2.882428|53.566826,%20-2.882400|53.566851,%20-2.883270|53.566279,%20-2.883316|53.565392,%20-2.882946|53.564111,%20-2.881889|53.558700,%20-2.875358|53.559236,%20-2.874431|53.558788,%20-2.873575|53.558494,%20-2.873682|53.557861,%20-2.872448|53.558369,%20-2.871409|53.557289,%20-2.869295|53.557356,%20-2.869104|53.557308,%20-2.868906|53.557222,%20-2.868847|53.557136,%20-2.868901|53.556779,%20-2.868713|53.556425,%20-2.868917|53.555682,%20-2.870070|53.558654,%20-2.875365|53.564029,%20-2.881800|53.565985,%20-2.883313|53.566853,%20-2.883273|53.567326,%20-2.882811|53.567277,%20-2.882425");
                /** New Map Route without Bus Station **/
                $embed->setImage("https://maps.googleapis.com/maps/api/staticmap?center=53.562528,-2.875634&size=500x400&key=" . $config['map_key'] . "&markers=color:blue%7C" .  $lat . "," . $long . "&zoom=14&path=color:0x0000ff|weight:3|53.565512,%20-2.883000|53.565564,%20-2.882788|53.565175,%20-2.881677|53.566807,%20-2.881598|53.566826,%20-2.882400|53.566851,%20-2.883270|53.566279,%20-2.883316|53.565392,%20-2.882946|53.564111,%20-2.881889|53.558700,%20-2.875358|53.559236,%20-2.874431|53.558788,%20-2.873575|53.558494,%20-2.873682|53.557861,%20-2.872448|53.558369,%20-2.871409|53.557289,%20-2.869295|53.557356,%20-2.869104|53.557308,%20-2.868906|53.557222,%20-2.868847|53.557136,%20-2.868901|53.556779,%20-2.868713|53.556425,%20-2.868917|53.555682,%20-2.870070|53.558654,%20-2.875365|53.564029,%20-2.881800|53.565985,%20-2.883313|53.566853,%20-2.883273|53.567326,%20-2.882811|53.567277,%20-2.882425");
                
                $interaction->respondWithMessage(MessageBuilder::new()
                    ->setContent("EL1 Bus Data")
                    ->addEmbed($embed)
                );
            }
        } catch(Exception $exception) {
            $interaction->respondWithMessage(MessageBuilder::new()
                ->setContent("Unable to find Bus! The most common cause of this is that the bus isn't running / has finished for the day, please try again later!")
            );
        }
    }
});
});

$discord->run();
