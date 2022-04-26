<?php
require_once "businesslogic/mainLogic.php";

$payload = null;
$logic = new MainLogic();
$result = null;

// REQUEST HANDLING
if ($_SERVER["REQUEST_METHOD"] == "GET"){
    isset($_GET["method"]) ? $method = $_GET["method"] : $method = "";
    isset($_GET["param"]) ? $param = $_GET["param"] : $param = "";
    $result = $logic->handleGetRequest($method, $param);

} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payload = json_decode(file_get_contents('php://input'));
    $result = $logic->handlePostRequest($payload);
} else {
    http_response_code(405);
    echo ("Method not supported yet!");
    exit();
}

//RESPONSE HANDLING
if ($result == null) {
    response(400, null);
} else {
    response(200, $result);
}

function response($httpStatus, $data)
{
    header('Content-Type: application/json');
    http_response_code($httpStatus);
    echo (json_encode($data));
}