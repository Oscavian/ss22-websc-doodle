<?php

class Appointment {
    private $db;
    private $handler;
    private $app_id;
    private $title;
    private $creator;
    private $description;
    private $location;
    private $creation_date;
    private $expiration_date;
    private $timeslots = [];
    private $participants = [];
    private $comments = [];
    public $isExpired;
    public $isValid;

    public function __construct(AppointmentHandler $handler, $id = null) {
        $this->handler = $handler;
        $this->db = $this->handler->getDb();
        $this->app_id = null;
        if ($this->db->select("SELECT * FROM appointments WHERE app_id = ?", [$id], "i") == null){
            $this->isValid = false;
        } else {
            $this->isValid = true;
            $this->app_id = $id;
        }
    }

    public function getBaseData() : ?array{
        if (!isset($this->app_id)){
            return null;
        }
        $result = $this->db->select("SELECT * FROM appointments WHERE app_id=?", [$this->app_id], "i", true);

        $this->title = $result["title"];
        $this->creator = $result["creator"];
        $this->description = $result["description"];
        $this->location = $result["location"];
        $this->creation_date = $result["creation_date"];
        $this->expiration_date = $result["expiration_date"];

        if (strtotime($this->creation_date) > strtotime($this->expiration_date)){
            $this->isExpired = true;
        } else {
            $this->isExpired = false;
        }

        $result["isExpired"] = $this->isExpired;

        return $result;
    }

    public function getFullDetails(): ?array {
        if (!isset($this->app_id)){
            return null;
        }
        $data = $this->getBaseData();

        $data["timeslots"] = $this->getTimeslots();
        $data["comments"] = $this->getComments();
        $data["participants"] = $this->getParticipants();

        return $data;
    }

    public function getParticipants(): ?array {
        if (!isset($this->app_id)){
            return null;
        }

        $chosen_timeslots = $this->db->select("select user_id, username, slot_id, app_id from timeslots join chosen_timeslots using(slot_id) join participants using(user_id) where app_id = ?", [$this->app_id], "i");
        foreach ($chosen_timeslots as $chosen){
            //avoid duplicate participants
            if ($this->containsParticipant($chosen["user_id"])){
                $i = $this->getParticipantIndex($chosen["user_id"]);
                $this->participants[$i]->addSlot($chosen["slot_id"]);
            } else {
                $new_user = new Participant($chosen["user_id"], $chosen["username"]);
                $new_user->addSlot($chosen["slot_id"]);
                $this->participants[] = $new_user;
            }
        }

        return $this->participants;
    }

    public function getComments(): ?array {
        if (!isset($this->app_id)){
            return null;
        }

        $comms = $this->db->select("select * from comments join participants using(user_id) where app_id = ?", [$this->app_id], "i", false);
        if (isset($comms)) {
            foreach ($comms as $comm) {
                $this->comments[] = new Comment($comm["username"], $comm["app_id"], $comm["message"]);
            }
        }

        return $this->comments;
    }

    public function getTimeslots(): ?array {
        if (!isset($this->app_id)){
            return null;
        }
        
        $slots = $this->db->select("select * from timeslots where app_id = ?", [$this->app_id], "i");
        
        if (isset($slots)){
            foreach ($slots as $ts_data){
                $this->timeslots[] = new Timeslot($ts_data["slot_id"], $ts_data["app_id"], $ts_data["start_datetime"], $ts_data["end_datetime"]);
            }
        }

        return $this->timeslots;
    }

    public function getId() {
        return $this->app_id;
    }

    public function containsParticipant($user_id): bool
    {
        foreach ($this->participants as $part){
            if ($part->id == $user_id){
                return true;
            }
        }
        return false;
    }

    public function getParticipantIndex($id): int
    {
        $counter = 0;
        foreach ($this->participants as $participant){
            if ($participant->id == $id){
                return $counter;
            }
            $counter++;
        }
        return -1;
    }
}