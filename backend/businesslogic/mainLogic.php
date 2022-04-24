<?php
include("db/database.php");
require_once "appointmentHandler.php";

class MainLogic {

    private $dh;
    private $appointmentHandler;
    function __construct() {
        $this->appointmentHandler = new AppointmentHandler();
        $this->dh = new DataHandler();
    }

    public function handleGetRequest(string $method, $param): ?array {
        $this->sanitizeGetArray();
        switch ($method) {
            case "getAppointmentList":
                $res = $this->appointmentHandler->getAppointmentList();
                break;
            case "getAppointmentById":
                $res = $this->appointmentHandler->getAppointmentDetailsById($param);
                break;
            default:
                $res = null;
                break;
        }
        return $res;
    }

    public function handlePostRequest(object $payload): array {
        $this->sanitizePayload($payload);
        switch ($payload->method){
            case "newAppointment":
                $res = $this->appointmentHandler->newAppointment($payload);
                break;
            case "addVotes":
                $res = $this->appointmentHandler->addVotes($payload);
                break;
            case "addComment":
                break;
            default:
                $res = null;
                break;
        }

        return $res;
    }

    private function sanitizePayload(&$payload){
        //TODO: fix
        /*foreach ( as $key => $val){
            if (!is_array($payload->key)){
                $payload->key = $this->test_input($val);
            }
        }
        return $payload;*/
    }

    private function sanitizeGetArray(){
        foreach ($_GET as $key => $value){
            $_GET[$key] = $this->test_input($value);
        }
    }

    public function test_input($data): string {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}



