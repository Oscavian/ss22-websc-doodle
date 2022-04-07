<?php
include("businesslogic/mainLogic.php");

$param = "";
$method = "";
$payload = null;
$logic = new MainLogic();

// REQUEST HANDLING
if ($_SERVER["REQUEST_METHOD"] == "GET"){
    isset($_GET["method"]) ? $method = $_GET["method"] : false;
    isset($_GET["param"]) ? $param = $_GET["param"] : false;

    $result = $logic->handleGetRequest($method, $param);
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //isset($_POST["method"]) ? $method = $_POST["method"] : false;

    $payload = json_decode(file_get_contents('php://input'));

    $result = $logic->handlePostRequest($payload);
}

//RESPONSE HANDLING

if ($result == null) {
    response("GET", 400, null);
} else {
    response("GET", 200, $result);
}

function response($method, $httpStatus, $data)
{
    header('Content-Type: application/json');
    switch ($method) {
        case "GET":
            http_response_code($httpStatus);
            echo (json_encode($data));
            break;
        default:
            http_response_code(405);
            echo ("Method not supported yet!");
    }
}