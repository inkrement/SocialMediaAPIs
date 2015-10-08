<?php

/*
 * Access Token wird benötigt. Ein temporärer kann unter
 * https://developers.facebook.com/tools/explorer/   gefunden werden.
 */
$access_token = "";

$http_response = file_get_contents("https://graph.facebook.com/v2.4/wuwienimsm/feed?access_token=".$access_token);

/*
 * Rückgabewert (Zeichenkette) wird in natives PHP (assoziatives) Array konvertiert
 */
$data = json_decode($http_response);

/*
 * Assoziatives Array enthält einige Informationen, wie Privacy-Informationen,
 * Tags, Ort und Links. Weitere Informationen können hier gefunden werden:
 * https://developers.facebook.com/docs/graph-api/reference/v2.5/user/feed
 */
foreach ($data->data as $post){
 echo $post->message;
}

?>
