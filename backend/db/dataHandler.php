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
        //$this->connection->close();
    }


    public function getAppointmentList()
    {
        $stmt = $this->connection->prepare("Select * from appointments");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $appointments = array();
        while ($row = $result->fetch_assoc()){
            $app = new Appointment($row["app_id"], $row["title"], $row["creator"], $row["description"], $row["location"], strtotime($row["creation_date"]), strtotime($row["expiration_date"]));
            $appointments[] = $app;
        }
        return $appointments;
    }

    /**
     * @param $id int Appointment-ID
     * @return Appointment|null
     */
    public function getAppointmentById(int $id): ?Appointment
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

    /**
     * @param int $app_id
     * @param array $slot_ids
     * @param $username
     * @return bool|null
     */
    public function addVotes(int $app_id, array $slot_ids, $username): bool
    {

        $appointment = $this->getAppointmentById($app_id);
        if (!$appointment || $appointment->isExpired){
            return false;
        }

        //insert new user
        $stmt = $this->connection->prepare("insert into participants (username) values (?)");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();
        //select user_id
        $stmt = $this->connection->prepare("select user_id from participants order by user_id desc limit 1");
        $stmt->execute();
        $stmt->bind_result($new_user);
        $stmt->close();
        //insert into chosen_timeslots
        foreach ($slot_ids as $id){
            $stmt = $this->connection->prepare("insert into chosen_timeslots (user_id, slot_id) values (?, ?)");
            $stmt->bind_param("ii", $new_user, $id);
        }
        $stmt->close();
        return true;

    }

}
