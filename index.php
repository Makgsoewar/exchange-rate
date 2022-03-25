<?php

require __DIR__ . '/vendor/autoload.php';

use \Google\Cloud\Firestore\FirestoreClient;
use \Dotenv\Dotenv;

$dotenv = Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$projectId = getenv('GOOGLE_PROJECT_ID','firestore-app-id');

$client = new \GuzzleHttp\Client();
$res = $client->request('GET', 'https://bitpay.com/api/rates', []);
 
$log = new Monolog\Logger('name');
$log->pushHandler(new Monolog\Handler\StreamHandler('app.log', Monolog\Logger::WARNING));
$log->warning($res->getBody());
 
$toArray = json_decode($res->getBody(), true);

$rates = [];
foreach ($toArray as $key => $value) {
    if ($value['code'] == 'USD') {
        $rates['buy'] = number_format($value['rate'],2,'.','');
    }
    if ($value['code'] == 'EUR') {
        $rates['sell'] = number_format($value['rate'],2,'.','');
    }
}



$last_numbers = array_map(function($rate){
    return substr($rate, -1);
}, $rates);

$number = $last_numbers['buy'].$last_numbers['sell'];

# Your Google Cloud Platform project ID
// $serviceAccountPath = '/home/thinaung/Documents/firestore_keyfile.json';

$db = new FirestoreClient([
    // 'keyFilePath' => '/home/thinaung/Documents/firestore_keyfile.json',
    'projectId' => $projectId,
]);

$date = date('Y-m-d'); 
$time = date('H:i'); 

$data = [
    "buy" => $rates['buy'],
    "date_time" => date('Y-m-d H:i:s'),
    "number" => $number,
    "sell" => $rates['sell'],
];

$current = $db->collection('currency')->document('current');
$current->set($data);

$docRef = $db->collection('currency/daily/'.$date)->document($time);
$docRef->set($data);



$log->warning(json_encode($number));
