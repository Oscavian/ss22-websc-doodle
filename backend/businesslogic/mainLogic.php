<?php
include("db/dataHandler.php");

class MainLogic {

    private $dh;
    function __construct() {
        $this->dh = new DataHandler();
    }

    function handleRequest($method, $param) {
        switch ($method) {
            case "getAppointmentList":
                $res = $this->dh->getAppointmentList();
                break;
            case "getAppointmentById":
                $res = $this->dh->getAppointmentById($param);
                break;
            case "getAppointmentByName":
                //$res = $this->dh->getAppointmentByName($param);
                break;
            case "getTimeslotsByAppId":
                break;
            case "getCommentsbyAppId":
                break;
            case "checkIfExpiredByAppId":
                break;
            case "newAppointment":
                break;
            case "addTimeslot":
                break;
            case "addVotes":
                break;
            default:
                $res = null;
                break;
        }
        return $res;
    }
}
