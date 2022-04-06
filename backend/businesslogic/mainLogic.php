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
            case "getTimeslotsByAppId":
                break;
            case "getCommentsbyAppId":
                $this->dh->getCommentsByAppId($param);
                break;
            case "checkIfExpiredByAppId":
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
            case "addTimeslot":
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
     * @return array
     */
    private function newAppointment($payload): ?array
    {
        if (!isset($payload->title) ||
            !isset($payload->creator) ||
            !isset($payload->description) ||
            !isset($payload->location) ||
            !isset($payload->expiration_date)) {
            $res["success"] = false;
            $res["invalidPayload"] = true;
            return $res;
        }

        $creation_date = (int)date(time());
        $payload->expiration_date = strtotime($payload->expiration_date);

        if ($creation_date > $payload->expiration_date){
            $res["success"] = false;
            $res["invalidPayload"] = true;
            return $res;
        }

        $this->dh->addNewAppointment($payload->title, $payload->creator, $payload->description, $payload->location, $creation_date, $payload->expiration_date)
            ? $res["success"] = true : $res["success"] = false;
        return $this->dh->getAppointmentList();
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
        return $this->dh->getAppointmentById($payload->app_id);
    }
}



