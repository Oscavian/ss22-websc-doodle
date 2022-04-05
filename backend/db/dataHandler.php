<?php
include("./models/appointment.php");
include("./models/timeslot.php");
include("./models/participant.php");
class DataHandler
{

    private $demoApp;
    private $demoTimeslots;
    private $demoParticipants;

    public function __construct(){
        $this->demoApp = [
            new Appointment(1, "Spazieren", "Oskar", "Spazieren gehen im Wald", "Wien", "02.04.2022 15:35:00", "04.04.2022 00:00:00"),
            new Appointment(2, "Wandern", "Oskar", "Spazieren gehen im Wald", "Wien", "02.04.2022 15:35:00", "04.04.2022 00:00:00"),
            new Appointment(3, "Lernen", "Oskar", "Spazieren gehen im Wald", "Wien", "02.04.2022 15:35:00", "04.04.2022 00:00:00"),
            new Appointment(4, "Schlafen", "Oskar", "Spazieren gehen im Wald", "Wien", "02.04.2022 15:35:00", "04.04.2022 00:00:00"),

        ];

        $this->demoTimeslots = [
            new Timeslot(1, 1, "14:00:00", "17:00:00"),
            new Timeslot(2, 1, "14:00:00", "17:00:00"),
            new Timeslot(3, 1, "14:00:00", "17:00:00"),
            new Timeslot(4, 2, "14:00:00", "17:00:00"),
            new Timeslot(5, 3, "14:00:00", "17:00:00"),
        ];

        $this->demoParticipants = [
            new Participant(1, 1, "Thomas"),
            new Participant(2, 2, "Nico"),
            new Participant(3, 2, "Marlis"),
            new Participant(4, 3, "Jassi"),
            new Participant(5, 4, "David"),
            new Participant(6, 4, "Max"),
        ];
    }

    public function getAppointmentList() {
        return $this->demoApp;
    }

    public function getAppointmentById($id)
    {
        $result = null;
        foreach ($this->demoApp as $val) {
            if ($val->app_id == $id) {
                $result = $val;
            }
        }

        foreach ($this->demoTimeslots as $slot) {
            if ($slot->app_id == $id){
                foreach ($this->demoParticipants as $participant){
                    if ($slot->slot_id == $participant->slot_id){
                        $result->participants[] = $participant;
                    }
                }
                $result->timeslots[] = $slot;
            }
        }

        return $result;
    }

    public function addTimeslot($app_id, $start, $end){
        $new_slot = new Timeslot(10, $app_id, $start, $end);

        foreach ($this->demoApp as $app){
            if ($app->app_id == $app_id){
                $app->timeslots[] = $new_slot;
            }
        }
    }

    /*private function getDemoAppointments() {
        return $this->demoApp;
    }

    private function getDemoTimeslots() {
        $demodata = [
            new Timeslot(1, 1, "14:00:00", "17:00:00"),
            new Timeslot(2, 1, "14:00:00", "17:00:00"),
            new Timeslot(3, 1, "14:00:00", "17:00:00"),
            new Timeslot(4, 2, "14:00:00", "17:00:00"),
            new Timeslot(5, 3, "14:00:00", "17:00:00"),
        ];
        return $demodata;
    }

    private function getDemoParticipants() {
        $demodata = [
            new Participant(1, 1, "Thomas"),
            new Participant(2, 2, "Nico"),
            new Participant(3, 2, "Marlis"),
            new Participant(4, 3, "Jassi"),
            new Participant(5, 4, "David"),
            new Participant(6, 4, "Max"),
        ];

        return $demodata;
    }*/


}
