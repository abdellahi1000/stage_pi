<?php
// test_update_offer.php
session_start();
$_SESSION['user_id'] = 19; // SEYID admin
$_SESSION['company_id'] = 19;
$_SESSION['user_type'] = 'entreprise';
$_SESSION['user_role'] = 'Administrator';
$_SESSION['logged_in'] = true;

$url = 'http://localhost/stage_pi/api/offres.php';
$data = [
    'id' => 52, // From my previous query
    'titre' => 'python UPDATED',
    'description' => 'updated description'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
