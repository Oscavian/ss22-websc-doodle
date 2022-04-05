<?php

class Participant {
    public $id;
    public $username;
    public $slot_id;

    public function __construct($id, $slot_id , $username) {
        $this->slot_id = $slot_id;
        $this->id = $id;
        $this->username = $username;
    }

}