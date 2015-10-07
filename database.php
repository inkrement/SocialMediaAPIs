<?php
/*
 * Access Token wird benötigt. Ein temporärer kann unter
 * https://developers.facebook.com/tools/explorer/   gefunden werden.
 */
$access_token = "";

try {
    /*
     * Datenbank im Arbeitsspeicher erstellen und errormode sowie Ausnahmen
     * Behandlung einstellen.
     */
    $db = new PDO('sqlite::memory:');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /*
     * Tabelle für Posts erstellen
     */
    $db->exec("CREATE TABLE posts (
                    id INTEGER PRIMARY KEY,
                    text TEXT
                  )");

    /*
     * SQL-Befehle für das Einfügen von Datensätzen vorbereiten und Parameter binden
     */
    $sql_insert = "INSERT INTO posts (text) VALUES (:text)";
    $stmt = $db->prepare($sql_insert);
    $stmt->bindParam(':text', $text);


    /*
     * Daten von Facebook laden und in Datenbank ablegen
     */
    $http_response = file_get_contents("https://graph.facebook.com/v2.4/wuwienimsm/feed?access_token=".$access_token);
    $data = json_decode($http_response);

    foreach ($data->data as $post){
     $text = $post->message;
     $stmt->execute();
    }

    /*
     * Alle posts aus Datenbank laden
     */
    $result = $db->query('SELECT * FROM posts');

    foreach($result as $row) {
      echo $row["text"];
    }

    /*
     * Tabelle löschen
     */
    $db->exec("DROP TABLE posts");

    /*
     * Verbindung schließen (Speicher freigeben)
     */
    $db = null;
  }
	catch(PDOException $e) {
		// Print PDOException message
		echo $e->getMessage();
  }

?>
