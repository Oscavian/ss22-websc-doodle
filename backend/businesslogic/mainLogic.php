<?php
include("db/database.php");
require_once "appointmentHandler.php";

class MainLogic {

    private $appointmentHandler;
    function __construct() {
        $this->appointmentHandler = new AppointmentHandler();
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

    public function handlePostRequest(object $payload): ?array {
        $this->sanitizePayload($payload);
        switch ($payload->method){
            case "newAppointment":
                $res = $this->appointmentHandler->newAppointment($payload);
                break;
            case "addVotes":
                $res = $this->appointmentHandler->addVotes($payload);
                break;
            default:
                $res = null;
                break;
        }

        return $res;
    }

    /**
     * @param $payload
     *  iterates through all $key => $val pairs including arrays of objects or strings
     *  does NOT search through payload recursively, max. Depth is 1 (0 => root), so only for usage in this specific project
     * @return void
     */
    private function sanitizePayload(&$payload){
        foreach (get_object_vars($payload) as $key => $val){
            if (is_array($val)){
                foreach ($val as $v){
                    if (is_object($v)){
                        foreach (get_object_vars($v) as $k => $w) {
                            $v->$k = $this->test_input($w);
                        }
                    } else {
                        $v = $this->test_input($v);
                    }
                }
            } else {
                $payload->$key = $this->test_input($val);
            }
        }
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



