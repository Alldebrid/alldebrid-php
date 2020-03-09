<?php

/*
    Links methods, as documented here https://docs.alldebrid.com/v4/#links

    You can use this library with links in 2 ways : 

    1. With api methods directly
    2. With link helper ( $alldebrid->link() ) and work with the Link object

    You can execute the code is this example page safely, but it's better is replace the agent and apikey with your own.
   
*/

// Include standalone lib or include autoloader if installed with composer 
require __DIR__ . '/../alldebrid.standalone.php'; // OR autoload : require __DIR__ . '/../vendor/autoload.php';

// apiShowcase credentials, replace with your own as those credentials will only return static example response
$agent = 'apiShowcase'; // Replace with your app / script name
$apikey = 'apiShowcaseStaticApikey';

$alldebrid = new \Alldebrid\Alldebrid($agent, $apikey);

$baseLink = 'https://example.com/classic';

// Example links that works on Alldebrid - try to use them !
$baseLink = 'https://example.com/classic';
$delayedLink = 'https://example.com/delayed';
$streamLink = 'https://example.com/stream';
$delayedStreamLink = 'https://example.com/streamDelayed';


// 1. With api methods directly

[ $isSupported, $error ] = $alldebrid->linkIsSupported($baseLink);

if($error) {
    $errorMessage = $user;
    die("Error " . $error . " : " . $errorMessage . "\n");
}

if($isSupported)
    echo "Link " . $baseLink . " is supported\n";
else 
    echo "Link " . $baseLink . " is not supported\n";


[ $type, $error ] = $alldebrid->linkType($baseLink);

 echo "Link " . $baseLink . " is of type " . $type . "\n";

[ $infos, $error ] = $alldebrid->linkInfos($baseLink);
[ $dlInfos, $error ] = $alldebrid->linkUnlock($baseLink);

if(isset($dlInfos['streams']) AND is_array($dlInfos['streams']) AND count($dlInfos['streams']) > 0)
    [ $dlInfos, $error ] = $alldebrid->linkStream($dlInfos['id'], $dlInfos['streams'][0]['id']);

// Delayed link need a loop
if(isset($dlInfos['delayed'])) {
    do {
        [ $delayedInfos, $error ] = $alldebrid->linkDelayed($dlInfos['delayed']);
    } while(!$error AND $delayedInfos['status'] != 2);
    
    // Or use linkWaitForDelayed that waits for you -- juste make sure your script can run for more than a few minutes
    [ $dlInfos, $error ] = $alldebrid->linkWaitForDelayed($dlInfos['delayed']);

    // Same as before but with regular callback to display progress
    $progressCallback = function($timeLeft) {
        echo "Delayed link processing, estimated waiting time " . $timeLeft . "s\n";
    };
    [ $dlInfos, $error ] = $alldebrid->linkWaitForDelayed($dlInfos['delayed'], $progressCallback);
}


// 2. With link helper ( $alldebrid->link() ) and work with the Link object

$link = $alldebrid->link($baseLink);
$link = $alldebrid->link($delayedLink);
$link = $alldebrid->link($streamLink);
$link = $alldebrid->link($delayedStreamLink);

[ $isSupported, $error ] = $link->isSupported();

if($error) {
    $errorMessage = $isSupported;
    die("Could not get link status " . $error . " : " . $errorMessage . "\n");
}

if($isSupported) {
    [ $type, $error ] = $link->type();
    
    if($error) {
        $errorMessage = $isSupported;
        die("Could not get link status " . $error . " : " . $errorMessage . "\n");
    }

    echo "Link of type " . $type . "\n";

    if($type == 'redirector') {
        // Funky shit 

    } else {
        [ $infos, $error ] = $link->infos();

        if($error) {
            $errorMessage = $infos;
            die("Could not get link infos " . $error . " : " . $errorMessage . "\n");
        }

        echo "Link is up, filename " . $infos['filename'] . "\n";

        [ $unlock, $error ] = $link->unlock();

        if($error) {
            $errorMessage = $infos;
            die("Could not unlock link " . $error . " : " . $errorMessage . "\n");
        }

        if($link->hasMultipleStreams === true) {
            $streams = $unlock['streams'];
            
            $streamID = $streams[0]['id'];

            echo "File has multiple stream, choosing stream #" . $streamID . "\n";

            [ $unlock, $error ] = $link->stream($streamID);
        }

        if($link->isDelayed === true) {
            echo "Link is delayed, waiting for it\n";

            [ $unlock, $error ] = $link->waitFordelayed();
        }

        if(!empty($unlock['link'])) {
            echo "Download link : " . $unlock['link'] . "\n";
        } else {
            echo "Could not get download link\n";
        }
    }
}