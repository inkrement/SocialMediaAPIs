<?php

//Abhängigkeiten laden
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Entitäten
 */
class Post{
  private $id;
  private $text;
  private $date;
  private $sentiment;

  public function __construct($id, $text, $date, $sentiment=NULL){
    $this->id = $id;
    $this->text = $text;
    $this->date = $date;
    $this->sentiment = $sentiment;
  }

  public function setSentiment($sentiment){
    $this->sentiment = $sentiment;
  }
  public function getId(){
    return $this->id;
  }

  public function getText(){
    return $this->text;
  }

  public function getDate(){
    return $this->date;
  }

  public function getSentiment(){
    return floatval($this->sentiment);
  }
}

class Comment{
  private $author;
  private $text;

  public function __construct($author, $text){
    $this->author = $author;
    $this->text = $text;
  }

  public function getAuthor(){
    return $this->author;
  }

  public function getText(){
    return $this->text;
  }
}

/**
 * Abstraktions Klasse die eine Verbindung zur Datenbank verwaltet.
 * Es wurde PDO Treiber verwendet, sodass schnell auf eine andere Datenbank
 * umgestellt werden kann (MySQL, SQLite, ...)
 */
class PostDAO{
  private $pdo;
  private $insert_sql;

  public function __construct(){
      //$this->pdo = new PDO('mysql:host=example.com;dbname=database', 'user', 'password');
      $this->pdo = new PDO('sqlite::memory:');

      //Tabellen erstellen und SQL Befehle vorbereiten
      $this->pdo->exec("CREATE TABLE posts (id TEXT, text TEXT, date TEXT, sentiment INTEGER)");
      $this->insert_sql = $this->pdo->prepare("INSERT INTO posts (id, text, date, sentiment) VALUES (:id, :text, :date, :sentiment)");
    }

  public function store($post){
    $this->insert_sql->bindValue(':id', $post->getId(), PDO::PARAM_STR);
    $this->insert_sql->bindValue(':text', $post->getText(), PDO::PARAM_STR);
    $this->insert_sql->bindValue(':date', $post->getDate(), PDO::PARAM_STR);
    $this->insert_sql->bindValue(':sentiment', $post->getSentiment(), PDO::PARAM_STR);

    $this->insert_sql->execute();
  }

  public function getAll(){
    $result = array();

    foreach ($this->pdo->query('SELECT * FROM posts') as $row )
        array_push($result, new Post($row['id'], $row['text'], $row['date'], $row['sentiment']));

    return $result;
  }

  public function __destruct(){
    $this->pdo->exec("DROP TABLE posts");
  }
}

/**
 * Scrape
 */
class FacebookScraping {
  const FB_API = "https://graph.facebook.com/v2.4/";
  private $access_token;

  public function __construct($access_token){
    $this->access_token = $access_token;
  }

  private function loadFromAPI($link){
    $http_response = file_get_contents(FacebookScraping::FB_API.$link."&access_token=".$this->access_token);
    return json_decode($http_response);
  }

  public function getPosts($site){
    $posts = array();
    $data = $this->loadFromAPI($site."/feed?d");

    foreach ($data->data as $post)
        array_push($posts, new Post($post->id, (isset($post->message))?$post->message:$post->story, $post->created_time));

    return $posts;
  }

  public function getComments($post_id){
    $comments = array();
    $data = $this->loadFromAPI($post_id."?fields=comments");

    if(!isset($data->comments->data)) return [];

    foreach ($data->comments->data as $comment){
      array_push($comments, new Comment($comment->from->name, $comment->message));
    }

    return $comments;
  }
}


/**
 * BEISPIEL
 */

// Access Token wird benötigt. Ein temporärer kann unter https://developers.facebook.com/tools/explorer/ gefunden werden.
$access_token = "";

//Facebook Scraping, Datenbank und Sentiment Analysis vorbereiten
$fb = new FacebookScraping($access_token);
$dao = new PostDAO();
$sentiment = new \PHPInsight\Sentiment();

//posts von bestimmter Facebook Seite laden
$posts = $fb->getPosts("MicrosoftAT");

//über alle posts iterieren, sentiment bestimmen und dann in Datenbank speichern
foreach ($posts as $post){
  //mit score können die genauen Zahlen eingesehen werden
	//$scores = $sentiment->score($post->getText());

  //alle Kommentare laden und bewerten.
  $comments = $fb->getComments($post->getId());
  $sum = 0;
  foreach($comments as $comment){
    switch($sentiment->categorise($comment->getText())){
      case "neg":
        $sum--;
        break;
      case "pos":
        $sum++;
    }
  }
  //als Resultat erhält man einen normierten Mittelwert im Bereich [-1;1]
  $sum = (0===$sum) ? $sum: $sum/count($comments);

  $post->setSentiment($sum);

  //in datenbank speichern
  $dao->store($post);
}

//alle Einträge aus der Datenbank laden und anzeigen
var_dump($dao->getAll());

?>
