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
            return [];
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
     * @return int|null
     */
    public function insert(string $query, array $params, string $param_types): ?int {

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param($param_types, ...$params);

        if ($stmt->execute()){
            $stmt->close();
            return $this->connection->insert_id;
        } else {
            $stmt->close();
            return null;
        }
    }
}
