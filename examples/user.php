<?php

// You can execute the code is this example page safely.

// Include standalone lib or include autoloader if installed with composer 
require __DIR__ . '/../alldebrid.standalone.php'; // OR autoload : require __DIR__ . '/../vendor/autoload.php';

// apiShowcase credentials, replace with your own as those credentials will only return static example response -- link saving won't work and stuff
$agent = 'apiShowcase'; // Replace with your app / script name
$apikey = 'apiShowcaseStaticApikey';

$alldebrid = new \Alldebrid\Alldebrid($agent, $apikey);

[ $user, $error ] = $alldebrid->user();

if($error) {
    $errorMessage = $user;
    die("Error " . $error . " : " . $errorMessage . "\n");
}

if($user['isPremium'])
    echo "Apikey working, hello " . $user['username'] . ", your subscription is active until " . date(DATE_RFC2822, $user['premiumUntil']) . "\n";
else
    echo "Apikey working, hello " . $user['username'] . ", buy a premium subscription to fully use all Alldebrid features !\n";

// Get hosts data
//[ $hosts, $error ] = $alldebrid->hosts();
//[ $hostsPriority, $error ] = $alldebrid->hostsPriority();


// Work with user saved links

$myLink = 'https://example.com/classic';

[ $savedLinks, $error ] = $alldebrid->userLinks();

if($error) {
    $errorMessage = $user;
    die("Error " . $error . " : " . $errorMessage . "\n");
}

echo "You have " . count($savedLinks) . " links saved for later. Saving new link " . $myLink . " and then deleting it\n";

[ $status, $error ] = $alldebrid->userLinksSave($myLink);

if($error) {
    $errorMessage = $status;
    die("Error " . $error . " : " . $errorMessage . "\n");
}

echo "Link saved successfully\n";

[ $savedLinks, $error ] = $alldebrid->userLinks();

if($error) {
    $errorMessage = $user;
    die("Error " . $error . " : " . $errorMessage . "\n");
}

echo "You now have " . count($savedLinks) . " links saved. Deleting example link " . $myLink . ".\n";

[ $status, $error ] = $alldebrid->userLinksDelete($myLink);

if($error) {
    $errorMessage = $status;
    die("Error " . $error . " : " . $errorMessage . "\n");
}

echo "Saved link was deleted successfully\n";