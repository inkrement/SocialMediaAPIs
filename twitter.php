<?php
/*
 * Twitter Bibliothek laden
 */
require_once('libs/TwitterAPIExchange.php');

/*
 * Im folgenden Block müssen die Zugangsdaten eingetragen werden.
 * Siehe: https://apps.twitter.com
 */
$settings = array(
    'oauth_access_token' => "",
    'oauth_access_token_secret' => "",
    'consumer_key' => "",
    'consumer_secret' => ""
);

/*
 * Hier wird nach Tweets gesucht die "microsoft" enthalten (HTTP Get Request)
 */
$url = 'https://api.twitter.com/1.1/search/tweets.json';
$getfield = '?q=microsoft';
$twitter = new TwitterAPIExchange($settings);
$response = $twitter->setGetfield($getfield)
             ->buildOauth($url, 'GET')
             ->performRequest();

/*
 * Die Schnittstelle liefert die Daten in Form von JSON aus, dieses Format kann
 * mit json_decode in ein natives (assoziatives) php Array konvertiert werden.
 */
$data = json_decode($response);



foreach ($data->statuses as $tweet){
 echo $tweet->text;
}
?>
