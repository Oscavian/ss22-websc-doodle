<?php

class DataHandler
{
    private $connection;

    public function __construct(){
        require_once "db/dbaccess.php";

        $this->connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if ($this->connection->connect_error) {
            die("DB Connection failed: " . $this->connection->connect_error);
        }
    }

    public function __destruct(){
        //$this->connection->close();
    }

    /**
     * @param string $query
     * @param array|null $params
     * @param string|null $param_types - for bind_param(), e.g. "ii" for 2 int params
     * @param bool|null $singleRow - if explicitly one row is expected, returns a single assoc arr if true
     * @return array|bool
     */
    public function select(string $query, array $params = null, string $param_types = null, bool $singleRow = null){

        $stmt = $this->connection->prepare($query);
        if (isset($params)) {
            $stmt->bind_param($param_types, ...$params);
        }

        if (!$stmt->execute()){
            return false;
        }

        $result = $stmt->get_result();
        $rows = array();
        $stmt->close();

        if (isset($singleRow) && $singleRow){
            return $result->fetch_assoc();
        } else if ($result->num_rows == 0){
            return null;
        } else {
            while ($row = $result->fetch_assoc()){
                $rows[] = $row;
            }

            return $rows;
        }
    }

    /**
     * @param string $query
     * @param array|null $params
     * @param string|null $param_types
     * @return bool
     */
    public function insert(string $query, array $params, string $param_types): bool {

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param($param_types, ...$params);

        if ($stmt->execute()){
            $stmt->close();
            return $this->connection->insert_id;
        } else {
            $stmt->close();
            return false;
        }
    }

    public function getLastInsertId(): int {
        return $this->connection->insert_id;
    }


    /**
     * @param string $title
     * @param string $creator
     * @param string $description
     * @param string $location
     * @param int $creation_date
     * @param string $expiration_date
     * @return bool
     */

    public function addNewAppointment(string $title, string $creator, string $description, string $location, string $creation_date, string $expiration_date, array $timeslots): bool
    {
        $stmt = $this->connection->prepare("insert into appointments (title, creator, description, location, creation_date, expiration_date) values (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $creator, $description, $location, $creation_date, $expiration_date);
        if(!$stmt->execute()){
            return false;
        }
        $stmt->reset();

        if (empty($timeslots)){
            return true;
        }

        $stmt = $this->connection->prepare("select app_id from appointments order by app_id desc limit 1");
        $stmt->execute();
        $stmt->bind_result($new_app_id);
        $stmt->fetch();
        $stmt->close();
        foreach ($timeslots as $slot){
            $stmt = $this->connection->prepare("insert into timeslots (app_id, start_datetime, end_datetime) values (?, ?, ?)");
            $stmt->bind_param("iss", $new_app_id, $slot->start_datetime, $slot->end_datetime);

            if (!$stmt->execute()) {
                return false;
            }
            $stmt->reset();
        }
        $stmt->close();
        return true;
    }

}
