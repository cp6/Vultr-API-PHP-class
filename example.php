<?php
require __DIR__ . '/vendor/autoload.php';

use Corbpie\VultrAPIv2\VultrAPI;

$vultr = new VultrAPI();

echo $vultr->listServers();//Data for all current account instances


$vultr->setSubid('31828385');//Must be set if interacting with a single instance actions
$vultr->serverReboot(); //Reboots/restarts instance with id:31828385


echo $vultr->responseAsString($vultr->serverDestroy());//Prints Success on HTTP 200 returned, else says Failed


$vultr->listRegions();//Returns regions
$vultr->listPlans();//Returns plans
$vultr->listISOs();//Returns ISO's
$vultr->listApps();//Returns Vultr one click apps
$vultr->listSnapshots();//Returns account snapshots
$vultr->listOS();//Returns vultr operating systems


//Creating a new instance
//First view:
$vultr->serverCreateOptions();
//For all options

//DC, plan and type are required

$vultr->serverCreateDC(19);//Sydney Australia location
$vultr->serverCreatePlan(202);//(2048 MB RAM,55 GB SSD,2.00 TB BW)
$vultr->serverCreateType('ISO', 146817);//Deploy with my custom ISO id:146817
$vultr->serverCreateLabel('Created with API');//label instance as "Created with API"
echo $vultr->serverCreate();//Creates instance/server with parameters set above (returns subid)

