<?php

class Appointment {
    public $app_id;
    public $title;
    public $creator;
    public $description;
    public $location;
    public $creation_date;
    public $expiration_date;
    public $timeslots;
    public $participants;
    public $comments;

    public function __construct($id, $title, $creator, $description, $location, $creation_date, $expiration_date){
        $this->app_id = $id;
        $this->title = $title;
        $this->creator = $creator;
        $this->description = $description;
        $this->location = $location;
        $this->creation_date = date("Y-m-d H:i:s", $creation_date);
        $this->expiration_date = date("Y-m-d H:i:s", $expiration_date);

        $this->participants = array();
        $this->comments = array();
        $this->timeslots = array();
    }

    public function getAppointmentId(){
        return $this->app_id;
    }

    public function getTitle(){
        return $this->title;
    }

    public function getCreator(){
        return $this->creator;
    }

    public function getDescription(){
        return $this->description;
    }

    public function getLocation(){
        return $this->location;
    }

    public function getCreationDate(){
        return $this->creation_date;
    }

    public function getExpirationDate(){
        return $this->expiration_date;
    }


}