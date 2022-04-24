<?php

class Timeslot {
    public $slot_id;
    public $app_id;
    public $start_datetime;
    public $end_datetime;

    public function __construct(int $slot_id, int $app_id, $start_datetime, $end_datetime){
        $this->slot_id = $slot_id;
        $this->app_id = $app_id;
        $this->start_datetime = date("Y-m-d H:i:s", strtotime($start_datetime));
        $this->end_datetime = date("Y-m-d H:i:s", strtotime($end_datetime));
    }
}