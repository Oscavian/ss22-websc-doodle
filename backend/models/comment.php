<?php

class Comment {
    public $username;
    public $app_id;
    public $message;

    public function __construct($username, $app_id, $message){
        $this->app_id = $app_id;
        $this->username = $username;
        $this->message = $message;
    }

}