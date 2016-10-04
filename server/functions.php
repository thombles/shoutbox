<?php

require_once 'helpers.php';
require_once 'db.php';

class Message {
    public $id;
    public $text;
    public $image;
    public $time;
    public $poster;

    function __construct($data) {
        $this->id = (int)$data['id'];
        $this->text = $data['text'];
        $this->image = $data['image'];
        $this->time = $data['time'];
	$this->poster = htmlentities($data['poster']);

	$this->text = htmlentities($this->text);
	$this->text = autolink($this->text);
	$this->text = emote($this->text);
    }
}

function postMessage($text, $poster, $image = null) { 
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $conn = Database::getFactory()->getConnection();
    
    $text = mysqli_real_escape_string($conn, $text);
    $poster = mysqli_real_escape_string($conn, $poster);
    $image = mysqli_real_escape_string($conn, $image);
    $ip = mysqli_real_escape_string($conn, $ip);
    $user_agent = mysqli_real_escape_string($conn, $user_agent);
    
    $sql = "INSERT INTO messages (text, poster, image, time, ip, user_agent) VALUES ('$text', '$poster', '$image', NOW(), '$ip', '$user_agent')";

    if (mysqli_query($conn, $sql)) {
        deleteMessages();
    }
}

function getMessages() {
    $conn = Database::getFactory()->getConnection();
    $sql = "SELECT * FROM messages ORDER BY id DESC";

    $messages = array();
    
    foreach (mysqli_fetch_all(mysqli_query($conn, $sql), MYSQLI_ASSOC) as $row) {
        $messages[] = new Message($row);
    }

    return $messages;
}

function getLastMessage() {
    $conn = Database::getFactory()->getConnection();
    $sql = "SELECT * FROM messages ORDER BY id DESC LIMIT 1";

    $result = mysqli_fetch_assoc(mysqli_query($conn, $sql));

    if ($result) {
        return new Message($result);
    }
    else  {
        return null;
    }
}

function deleteMessages() {
    $limit = getConfig('limit');

    $conn = Database::getFactory()->getConnection();
    $sql = "SELECT * FROM messages WHERE id NOT IN (SELECT id FROM (SELECT id FROM messages ORDER BY id DESC LIMIT $limit) temp)";

    foreach (mysqli_fetch_all(mysqli_query($conn, $sql), MYSQLI_ASSOC) as $row) {
        $message = new Message($row);

        if ($message->image) {
            try {
                unlink('uploads/' . $message->image);
            }
            catch(Exception $e) {
            }
        }

        mysqli_query($conn, "DELETE FROM messages WHERE id = $message->id");
    }
}

function saveImage($tmp, $name, $resizeWidth, $poster) {
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $dest = 'uploads/' . $filename;

    if ($ext == 'jpg' || $ext == 'jpeg') {
        $img = imagecreatefromjpeg($tmp);
    }
    else if ($ext == 'png') {
        $img = imagecreatefrompng($tmp);
    }

    $width  = imagesx($img);  
    $height = imagesy($img);

    if ($resizeWidth > $width) $resizeWidth = $width;

    $resizeHeight = $resizeWidth * ($height / $width);

    $resized = imagecreatetruecolor($resizeWidth, $resizeHeight);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $width, $height); 

    if ($ext == 'jpg' || $ext == 'jpeg') {
        imagejpeg($resized, $dest);
    }
    else if ($ext == 'png') {
        imagepng($resized, $dest);
    }

    postMessage(null, $poster, $filename);
}

?>