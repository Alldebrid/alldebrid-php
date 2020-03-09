<?php

// Include standalone lib or include autoloader if installed with composer 
require __DIR__ . '/../alldebrid.standalone.php'; // OR autoload : require __DIR__ . '/../vendor/autoload.php';

// apiShowcase credentials, replace with your own as those credentials will only return static example response
$agent = 'apiShowcase'; // Replace with your app / script name
$apikey = 'apiShowcaseStaticApikey';

$alldebrid = new \Alldebrid\Alldebrid($agent, $apikey);

// The Alldebrid library use Go-style error : all function return an array [ $response, $error ]
// First check the $error. If everything went OK, $error === null, and the response is in $response
// If $error is set to an error code, then $response will have the error message or error content

// To see all possible API error, check https://docs.alldebrid.com/v4/#all-errors
// More over, this lib emit LIB_ERROR errors when you misuse it

[ $infos, $error ] = $alldebrid->error(); // Trigguer generic error

[ $infos, $error ] = $alldebrid->error('LINK_HOST_NOT_SUPPORTED'); // Trigguer specific error

if($error) {
    $errorMessage = $infos;
    //die("Error " . $error . " : " . $errorMessage . "\n");
}

// Infos are OK if no error was returned
// print_r($infos);


// Alternatively, you can use an Exception mode if we prefer

$alldebrid->setErrorMode('exception');

try {
    $infos = $alldebrid->error('LINK_HOST_NOT_SUPPORTED');
} catch(Exception $e) {
    die("Exception " . $e->getMessage() . "\n");
}
