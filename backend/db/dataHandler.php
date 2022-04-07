<?php
include("./models/appointment.php");
include("./models/timeslot.php");
include("./models/participant.php");
include("./models/comment.php");
class DataHandler
{
    private $connection;

    public function __construct(){
        require("db/dbaccess.php");

        $this->connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if ($this->connection->connect_error) {
            die("DB Connection failed: " . $this->connection->connect_error);
        }
    }

    public function __destruct(){
        $this->connection->close();
    }


    public function getAppointmentList()
    {
        $stmt = $this->connection->prepare("Select * from appointments");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $appointments = array();
        while ($row = $result->fetch_assoc()){
            $appointments[] = $row;
        }
        return $appointments;
    }

    /**
     * @param $id int Appointment-ID
     * @return Appointment|null
     */
    public function getAppointmentById($id): ?Appointment
    {
        $stmt = $this->connection->prepare("SELECT * from appointments where app_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        //no matching appointment found
        if ($result->num_rows != 1){
            return null;
        }

        $data = $result->fetch_assoc();

        $appointment = new Appointment($data["app_id"], $data["title"], $data["creator"], $data["description"], $data["location"], strtotime($data["creation_date"]), strtotime($data["expiration_date"]));

        $stmt = $this->connection->prepare("select * from timeslots where app_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        // fetch timeslots
        while ($ts_data = $result->fetch_assoc()){
            $new_ts = new Timeslot($ts_data["slot_id"], $ts_data["app_id"], strtotime($ts_data["start_datetime"]), strtotime($ts_data["end_datetime"]));
            $appointment->timeslots[] = $new_ts;
        }

        // fetch participants

        $stmt = $this->connection->prepare("select user_id, username, slot_id, app_id from timeslots join chosen_timeslots using(slot_id) join participants using(user_id) where app_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($part_data = $result->fetch_assoc()){
            //check if the participant has already been added to the array
            if ($appointment->containsParticipant($part_data["user_id"])){
                $i = $appointment->getParticipantIndex($part_data["user_id"]);
                $appointment->participants[$i]->addSlot($part_data["slot_id"]);
            } else {
                $new_part = new Participant($part_data["user_id"], $part_data["username"]);
                $new_part->addSlot($part_data["slot_id"]);
                $appointment->participants[] = $new_part;
            }
        }

        // fetch comments
        $stmt = $this->connection->prepare("select * from comments join participants using(user_id) where app_id = ?");
        $stmt->bind_param("i", $app);
        $app = $appointment->app_id;
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($comm_data = $result->fetch_assoc()){
            $new_comm = new Comment($comm_data["username"], $part_data["app_id"], $comm_data["message"]);
            $appointment->comments[] = $new_comm;
        }

        return $appointment;

    }

    public function addTimeslot($app_id, $start, $end){
        /*$new_slot = new Timeslot(10, $app_id, $start, $end);

        foreach ($this->demoApp as $app){
            if ($app->app_id == $app_id){
                $app->timeslots[] = $new_slot;
            }
        }*/
        return null;
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

    public function addNewAppointment(string $title, string $creator, string $description, string $location, int $creation_date, string $expiration_date): ?bool
    {   /*
        $ids = array();
        foreach ($this->demoApp as $appointment){
            $ids[] = $appointment->app_id;
        }
        $new_id = max($ids) + 1;

        $new_app = new Appointment($new_id, $title, $creator, $description, $location, $creation_date, $expiration_date);
        $this->demoApp[] = $new_app;

        if ($new_app->app_id == max($ids) + 1){
            return true;
        } else {
            return false;
        }
        */
        return null;
    }

    /**
     * @param int $app_id
     * @param array $slot_ids
     * @param $username
     * @return bool|null
     */
    public function addVotes(int $app_id, array $slot_ids, $username)
    {
        /*
        $appointment = $this->getAppointmentById($app_id);
        if (!$appointment){
            return false;
        }

        $appointment->participants[] = new Participant($slot_ids, $username);
        return true;
        */
        return null;

    }

    public function getCommentsByAppId($param)
    {
        return null;
    }
}
