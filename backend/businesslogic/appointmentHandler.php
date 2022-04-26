<?php
require_once "models/appointment.php";
require_once "models/timeslot.php";
require_once "models/comment.php";
require_once "models/participant.php";

class AppointmentHandler {
    private $db;

    public function __construct() {
        require_once "db/database.php";
        $this->db = new DataHandler();
    }

    public function getDb(): DataHandler {
        return $this->db;
    }

    public function getById(int $id): Appointment {
        return new Appointment($this, $id);
    }

    public function getAppointmentList(): array {
        $result = $this->db->select("Select app_id from appointments");
        $app_list = array();
        foreach ($result as $row) {
            $app_list[] = $this->getBaseDataById($row["app_id"]);
        }
        return $app_list;
    }

    public function getBaseDataById(int $id): array {
        $appointment = $this->getById($id);
        return $appointment->getBaseData();
    }

    public function getAppointmentDetailsById(int $id): array {

        $appointment = $this->getById($id);
        if (!$appointment->isValid) {
            return ["success" => false, "msg" => "Assignment with ID $id does not exist!", "inputInvalid" => true];
        }

        return $appointment->getFullDetails();
    }

    /**
     * @param $payload
     *   {
     *      "method": "newAppointment",
     *      "title": "Spazieren",
     *      "creator": "Lena",
     *      "description": "Spazieren gehen im Wald",
     *      "location": "Wien",
     *      "expiration_date": "19-04-2022 00:00:00",
     *      "timeslots": [
     *          {
     *              "start_datetime": "17-04-2022 12:00:00",
     *              "end_datetime": "17-04-2022 17:30:00"
     *          },
     *          {
     *              "start_datetime": "18-04-2022 13:00:00",
     *              "end_datetime": "18-04-2022 19:30:00"
     *          }
     *      ]
     *  }
     * @return array
     */

    public function newAppointment($payload): ?array {
        if (!isset($payload->title) ||
            !isset($payload->creator) ||
            !isset($payload->description) ||
            !isset($payload->location) ||
            !isset($payload->expiration_date) ||
            !isset($payload->timeslots) ||
            !is_array($payload->timeslots)) {
            $res["success"] = false;
            $res["invalidPayload"] = true;
            return $res;
        }


        $creation_date = date("Y-m-d H:i:s", time());
        $payload->expiration_date = date("Y-m-d H:i:s", strtotime($payload->expiration_date));
        //check if expired date is later than creation date
        if (strtotime($creation_date) > strtotime($payload->expiration_date)) {
            $res["success"] = false;
            $res["invalidPayload"] = true;
            return $res;
        }

        foreach ($payload->timeslots as $slot) {
            //format input date
            //TODO: maybe check if end is after start
            $slot->start_datetime = strtotime($slot->start_datetime);
            $slot->start_datetime = date("Y-m-d H:i:s", $slot->start_datetime);

            $slot->end_datetime = strtotime($slot->end_datetime);
            $slot->end_datetime = date("Y-m-d H:i:s", $slot->end_datetime);
        }
        $query = "insert into appointments (title, creator, description, location, creation_date, expiration_date) values (?, ?, ?, ?, ?, ?)";
        $new_app_id = $this->db->insert($query, [$payload->title, $payload->creator, $payload->description, $payload->location, $creation_date, $payload->expiration_date], "ssssss");

        if (empty($new_app_id)) {
            return ["success" => false, "invalidPayload" => true];
        }

        foreach ($payload->timeslots as $slot) {
            if (!$this->db->insert("insert into timeslots (app_id, start_datetime, end_datetime) values (?, ?, ?)", [$new_app_id, $slot->start_datetime, $slot->end_datetime], "iss")) {
                return ["success" => false, "invalidTimeslots" => true];
            }
        }

        return ["success" => true, "appointment_id" => $new_app_id, "msg" => "Appointment successfully created!"];
    }

    /**
     * @param object $payload JSON Format e.g.:
     * {
     *      "app_id": "1",
     *      "slot_ids": ["1", "3", "15"],
     *      "username": "Alice",
     *      "comment" : "Hello World"
     * }
     * @return array success boolean
     */

    public function addVotes(object $payload): array {
        if (!isset($payload->app_id) ||
            !isset($payload->slot_ids) ||
            !isset($payload->username) ||
            !is_numeric($payload->app_id) ||
            !is_array($payload->slot_ids)) {
            return ["success" => false, "invalidPayload" => true];
        }
        foreach ($payload->slot_ids as $id) {
            if (!is_numeric($id)) {
                return ["success" => false, "invalidPayload" => true, "msg" => "Invalid Slot IDs!"];
            }
        }

        $appointment = $this->getById($payload->app_id);
        if (!$appointment->isValid) {
            return ["success" => false, "msg" => "Appointment #$payload->app_id does not exist!"];
        }

        $new_user_id = $this->db->insert("insert into participants (username) values (?)", [$payload->username], "s");

        foreach ($payload->slot_ids as $id) {
            $this->db->insert("insert into chosen_timeslots (user_id, slot_id) values (?, ?)", [$new_user_id, $id], "ii");
        }

        if (isset($payload->comment)){
            $this->db->insert("insert into comments (user_id, message, app_id) values (?,?,?)", [$new_user_id, $payload->comment, $appointment->getId()], "isi");
        }

        return ["success" => true, "msg" => "Vote saved successfully!"];
    }

}