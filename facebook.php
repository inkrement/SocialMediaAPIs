<?php

/*
 * Access Token wird benötigt. Ein temporärer kann unter
 * https://developers.facebook.com/tools/explorer/   gefunden werden.
 */
$access_token = "";

$http_response = file_get_contents("https://graph.facebook.com/v2.4/wuwienimsm/feed?access_token=".$access_token);

$data = json_decode($http_response);
foreach ($data->data as $post){
 echo $post->message;
}

?>
