<?php

class Participant {
    //public $id;
    public $username;
    public $slot_ids;

    public function __construct(array $slot_ids, $username) {
        $this->slot_ids = $slot_ids;
        //$this->id = $id;
        $this->username = $username;
    }
}