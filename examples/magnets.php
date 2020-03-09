<?php

/*
    Magnets methods, as documented here https://docs.alldebrid.com/v4/#magnet

    You can use this library with links in 2 ways : 

    1. With api methods directly
    2. With magnet helper ( $alldebrid->magnet() ) and work with the Magnet object
   
*/


// /!\ /!\ This code is only to show how it works, it wont work as-is, file path dont exists, and magnets endpoints are fully static with apiShowcaseStaticApikey /!\ /!\
exit;



// Include standalone lib or include autoloader if installed with composer 
require __DIR__ . '/../alldebrid.standalone.php'; // OR autoload : require __DIR__ . '/../vendor/autoload.php';

// apiShowcase credentials, replace with your own as those credentials will only return static example response
$agent = 'apiShowcase'; // Replace with your app / script name
$apikey = 'apiShowcaseStaticApikey';

$alldebrid = new \Alldebrid\Alldebrid($agent, $apikey);

// Example magnets and files
$magnet1 = 'magnet:?xt=urn:btih:286d2e5b4f8369855328336ac1263ae02a7a60d5&dn=ubuntu-18.04.4-desktop-amd64.iso';
$magnet2 = 'magnet:?xt=urn:btih:e73108cbd628fee5cf203acdf668c5bf45d07810&dn=ubuntu-18.04.4-live-server-amd64.iso';

$file1 = '/some/dir/ubuntu-18.04.4-desktop-amd64.iso.torrent';
$file2 = '/some/dir/ubuntu-18.04.4-desktop-amd64.iso.torrent';


// 1. With api methods directly


// Magnet upload, single or multiple
[ $response, $error ] = $alldebrid->magnetUpload($magnet1);
[ $response, $error ] = $alldebrid->magnetUpload([
    $magnet1,
    $magnet2,
]);

// File upload, single or multiple, either with a path, or direclty passing the torrent file content 
[ $response, $error ] = $alldebrid->magnetUploadFile($file1);
[ $response, $error ] = $alldebrid->magnetUploadFile($file1, 'file'); // Same as before, 'file' is the default uplaod method
[ $response, $error ] = $alldebrid->magnetUploadFile([
    $file1,
    $file2,
], 'file'); // 'file' second param is optionnal as 'file' is the default

// When upload torrent file content directly, pass the 'inline' method to magnetUploadFile()
$fileContent1 = file_get_contents($file1);
$fileContent2 = file_get_contents($file2);
[ $response, $error ] = $alldebrid->magnetUploadFile($fileContent1, 'inline');
[ $response, $error ] = $alldebrid->magnetUploadFile([ $fileContent1, $fileContent2 ], 'inline');


[ $response, $error ] = $alldebrid->magnetInstant($magnet1);
[ $response, $error ] = $alldebrid->magnetInstant([
    $magnet1,
    $magnet2,
]);


[ $response, $error ] = $alldebrid->magnetStatus(); // Get all magnet status
$magnetID = $response[0]['id'];

[ $response, $error ] = $alldebrid->magnetStatus($magnetID); // Get status of a specfic magnet ID
[ $response, $error ] = $alldebrid->magnetDelete($magnetID);
[ $response, $error ] = $alldebrid->magnetRestart($magnetID); // Restart a magnet that is in error status






// 1. With magnet helper ( $alldebrid->magnet() ) and work with the Magnet object

$magnet = $alldebrid->magnet($magnet1);
$magnet2 = $alldebrid->magnet($file1);
$magnet3 = $alldebrid->magnet($magnetID);

[ $isAvailable, $error ] = $magnet->instant();

if($error) {
    $errorMessage = $isAvailable;
    die("Could not get magnet instant status " . $error . " : " . $errorMessage . "\n");
}

if($isAvailable) {
    echo "Magnet already available\n";

    [ $upload, $error ] = $magnet->upload();

    if($error) {
        $errorMessage = $isAvailable;
        die("Could not get upload magnet " . $error . " : " . $errorMessage . "\n");
    }

    $links = $magnet->links();
    print_r($links);
    exit;
} else {
    echo "Magnet will have to be processed\n";

    [ $upload, $error ] = $magnet->upload();

    if($error) {
        $errorMessage = $isAvailable;
        die("Could not get upload magnet " . $error . " : " . $errorMessage . "\n");
    }

    do {
        [ $isRunning, $error ] = $magnet->isRunning();
    } while(!$error AND $isRunning);

    if($error) {
        $errorMessage = $isRunning;
        die("Could not get track magnet status " . $error . " : " . $errorMessage . "\n");
    }

    // Magnet processing finished

    if($magnet->isReady()) {
        $links = $magnet->links();
        print_r($links);
        exit;
    } elseif($magnet->isError()) {
        echo "Restarting magnet\n";
        $magnet->restart();
        exit;
    }
}