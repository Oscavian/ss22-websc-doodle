<?php
include("db/dataHandler.php");

class MainLogic {

    private $dh;
    function __construct() {
        $this->dh = new DataHandler();
    }

    public function handleGetRequest(string $method, $param) {
        switch ($method) {
            case "getAppointmentList":
                $res = $this->dh->getAppointmentList();
                break;
            case "getAppointmentById":
                $res = $this->dh->getAppointmentById($param);
                break;
            default:
                $res = null;
                break;
        }
        return $res;
    }

    public function handlePostRequest(object $payload)
    {
        switch ($payload->method){
            case "newAppointment":
                $res = $this->newAppointment($payload);
                break;
            case "addVotes":
                $res = $this->addVotes($payload);
                break;
            default:
                $res["success"] = false;
                $res["invalidMethod"] = true;
                break;
        }

        return $res;
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
    private function newAppointment($payload): ?array
    {
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

        $creation_date = (int)date(time());
        $payload->expiration_date = strtotime($payload->expiration_date);

        //check if expired date is later than creation date
        if ($creation_date > $payload->expiration_date){
            $res["success"] = false;
            $res["invalidPayload"] = true;
            return $res;
        }

        $creation_date = date("Y-m-d H:i:s", $creation_date);
        $payload->expiration_date = date("Y-m-d H:i:s", $payload->expiration_date);

        foreach ($payload->timeslots as $slot){
            //format input date
            //TODO: maybe check if end is after start
            $slot->start_datetime = strtotime($slot->start_datetime);
            $slot->start_datetime = date("Y-m-d H:i:s", $slot->start_datetime);

            $slot->end_datetime = strtotime($slot->end_datetime);
            $slot->end_datetime = date("Y-m-d H:i:s", $slot->end_datetime);
        }

        $this->dh->addNewAppointment($payload->title, $payload->creator, $payload->description, $payload->location, $creation_date, $payload->expiration_date, $payload->timeslots)
            ? $res["success"] = true : $res["success"] = false;
        return $res;
    }

    /**
     * @param object $payload JSON Format e.g.:
     * {
     *      "app_id": "1",
     *      "slot_ids": ["1", "3", "15"],
     *      "username": "Alice"
     * }
     * @return array success boolean
     */

    private function addVotes(object $payload) {
        if (!isset($payload->app_id) ||
            !isset($payload->slot_ids) ||
            !isset($payload->username) ||
            !is_numeric($payload->app_id) ||
            !is_array($payload->slot_ids)) {
            $res["success"] = false;
            $res["invalidPayload"] = true;
            return $res;
        }

        $this->dh->addVotes($payload->app_id, $payload->slot_ids, $payload->username)
            ? $res["success"] = true : $res["success"] = false;


        $res["success"] = true;
        return $res;
    }
}



