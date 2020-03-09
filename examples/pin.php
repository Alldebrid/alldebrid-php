<?php

/*
    Pin auth is explained here https://docs.alldebrid.com/v4/#pin-auth

    You can use the pin auth to get an apikey in 4 ways : 

    1. With pin helper ( $alldebrid->pin() ) and manual loop
    2. With pin helper ( $alldebrid->pin() ) and automatic loop (can be long running, be sure your script can execute for ~10 min)
    3. With pin helper ( $alldebrid->pin() ) and automatic loop and regular callback (can be long running, be sure your script can execute for ~10 min)
    4. With api methods directly

    You can execute the code is this example page safely.

*/

// Include standalone lib or include autoloader if installed with composer 
require __DIR__ . '/../alldebrid.standalone.php'; // OR autoload : require __DIR__ . '/../vendor/autoload.php';


$agent = 'pinFlow'; // Replace with your app / script name
$alldebrid = new \Alldebrid\Alldebrid($agent); // No apikey given as we will get one from the PIN auth 


// With pin helper ( $alldebrid->pin() ) and manual loop

[ $pinCode, $error ] = $alldebrid->pin();

if($error) {
    $errorMessage = $pinCode;
    die("Could not get pin code, error " . $error . " : " . $errorMessage . "\n");
}

echo "Enter this pin code : " . $pinCode . ", remaining time 600s\n";

do {
    sleep(5);
    [ $isLoggued, $error ] = $alldebrid->isLoggued();
    if($alldebrid->pinExpiration > 0)
        echo "Still waiting, remaining time " . $alldebrid->pinExpiration . "s\n";
} while(!$error AND $isLoggued == false);

if($error) {
    $errorMessage = $isLoggued;
    die("Could not check pin code status, error " . $error . " : " . $errorMessage . "\n");
}

[ $user, $error ] = $alldebrid->user();
echo "Loggued as " . $user['username'] . ", apikey " . $alldebrid->apikey . "\n";
exit;





// With pin helper ( $alldebrid->pin() ) and automatic loop (can be long running, be sure your script can execute for ~10 min)

[ $pinCode, $error ] = $alldebrid->pin();

if($error) {
    $errorMessage = $pinCode;
    die("Could not get pin code, error " . $error . " : " . $errorMessage . "\n");
}

echo "Enter this pin code : " . $pinCode . " in the next 600s\n";

[ $isLoggued, $error ] = $alldebrid->waitForPin();

if($error) {
    $errorMessage = $isLoggued;
    die("Could not get pin code, error " . $error . " : " . $errorMessage . "\n");
}

[ $user, $error ] = $alldebrid->user();
echo "Loggued as " . $user['username'] . ", apikey " . $alldebrid->apikey . "\n";
exit;







// With pin helper ( $alldebrid->pin() ) and automatic loop and regular callback (can be long running, be sure your script can execute for ~10 min)

[ $pinCode, $error ] = $alldebrid->pin();

if($error) {
    $errorMessage = $pinCode;
    die("Could not get pin code, error " . $error . " : " . $errorMessage . "\n");
}

echo "Enter this pin code : " . $pinCode . " in the next 600s\n";

$progressCallback = function($expiresIn) {
    echo "Still waiting, remaining time " . $expiresIn . "s\n";
};

[ $isLoggued, $error ] = $alldebrid->waitForPin($progressCallback);

if($error) {
    $errorMessage = $isLoggued;
    die("Could not get pin code, error " . $error . " : " . $errorMessage . "\n");
}

[ $user, $error ] = $alldebrid->user();
echo "Loggued as " . $user['username'] . ", apikey " . $alldebrid->apikey . "\n";
exit;





// With api methods directly

[ $pinResponse, $error ] = $alldebrid->pinGet();

if($error) {
    $errorMessage = $pinResponse;
    die("Could not get pin code, error " . $error . " : " . $errorMessage . "\n");
}

echo "Enter this pin code : " . $pinResponse['pin'] . " in the next 600s\n";

do {
    sleep(5);
    [ $checkResponse, $error ] = $alldebrid->pinCheck($pinResponse['pin'], $pinResponse['check']);

    if(!$error AND $checkResponse['expires_in'] > 0)
        echo "Still waiting, remaining time " . $checkResponse['expires_in'] . "s\n";

} while(!$error AND $checkResponse['activated'] != true);

if($error) {
    $errorMessage = $checkResponse;
    die("Could not get pin code, error " . $error . " : " . $errorMessage . "\n");
}

$alldebrid->setApikey($checkResponse['apikey']);

[ $user, $error ] = $alldebrid->user();
echo "Loggued as " . $user['username'] . ", apikey " . $alldebrid->apikey . "\n";
exit;