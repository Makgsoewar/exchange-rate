<?php

require __DIR__ . '/vendor/autoload.php';

use \Google\Cloud\Firestore\FirestoreClient;

$client = new \GuzzleHttp\Client();
$res = $client->request('GET', 'https://bitpay.com/api/rates', []);

// Send an asynchronous request.
// $request = new \GuzzleHttp\Psr7\Request('GET', 'http://httpbin.org');
// $promise = $client->sendAsync($request)->then(function ($response) {
//     echo 'I completed! ' . $response->getBody();
// });

$toArray = json_decode($res->getBody(), true);

$data = [];
foreach ($toArray as $key => $value) {
    if ($value['code'] == 'USD' || $value['code'] == 'EUR') {
        $data[] = $value;
    }
}

# Your Google Cloud Platform project ID
$projectId = 'firestore-tut-5711d';
// $serviceAccountPath = '/home/thinaung/Documents/firestore_keyfile.json';


# Explicitly use service account credentials by specifying the private key
    
$db = new FirestoreClient([
    'projectId' => $projectId,
]);

$docRef = $db->collection('currency')->document('exchange_rate');
$docRef->set($data);

$log = new Monolog\Logger('name');
$log->pushHandler(new Monolog\Handler\StreamHandler('app.log', Monolog\Logger::WARNING));
$log->warning(json_encode($data));
