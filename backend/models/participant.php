<?php

class Participant {
    public $id;
    public $username;
    public $slot_ids;

    public function __construct($id, $username) {
        $this->username = $username;
        $this->id = $id;
        $this->slot_ids = array();
    }

    public function addSlot($slot_id){
        $this->slot_ids[] = $slot_id;
    }
}