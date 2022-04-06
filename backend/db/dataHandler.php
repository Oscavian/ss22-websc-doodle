<?php
include("./models/appointment.php");
include("./models/timeslot.php");
include("./models/participant.php");
include("./models/comment.php");
class DataHandler
{

    private $demoApp;
    private $demoTimeslots;
    private $demoParticipants;
    private $demoComments;

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

    /*public function __construct(){

        $this->demoApp = [
            new Appointment(1, "Spazieren", "Oskar", "Spazieren gehen im Wald", "Wien", strtotime("now"), strtotime("next monday")),
            new Appointment(2, "Wandern", "Oskar", "Spazieren gehen im Wald", "Wien", strtotime("now"), strtotime("Tomorrow")),
            new Appointment(3, "Lernen", "Oskar", "Spazieren gehen im Wald", "Wien", strtotime("now"), strtotime("+3 days")),
            new Appointment(4, "Schlafen", "Oskar", "Spazieren gehen im Wald", "Wien", strtotime("now"), strtotime("+1 month")),

        ];

        $this->demoComments = [
          new Comment("Oskar", 1, "Hello World!"),
          new Comment("Nico", 1, "Hello World!"),
          new Comment("Marlis", 1, "Hello World!"),
        ];

        $this->demoTimeslots = [
            new Timeslot(1, 1, "14:00:00", "17:00:00"),
            new Timeslot(2, 1, "14:00:00", "17:00:00"),
            new Timeslot(3, 1, "14:00:00", "17:00:00"),
            new Timeslot(4, 2, "14:00:00", "17:00:00"),
            new Timeslot(5, 3, "14:00:00", "17:00:00"),
        ];

        $this->demoParticipants = [
            new Participant(array(1), "Thomas"),
            new Participant(array(2, 1), "Nico"),
            new Participant(array(2), "Marlis"),
            new Participant(array(3), "Jassi"),
            new Participant(array(4), "David"),
            new Participant(array(4), "Max"),
        ];
    }*/

    public function getAppointmentList()
    {
        $stmt = $this->connection->prepare("Select * from appointments");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if($result->fetch_assoc()){
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }

    /**
     * @param $id int Appointment-ID
     * @return Appointment|null
     */
    public function getAppointmentById($id): ?Appointment
    {
        $result = null;
        foreach ($this->demoApp as $val) {
            if ($val->app_id == $id) {
                $result = $val;
            }
        }

        //TODO: check if Appointment is expired, if so, abort request

        if (!$result){
            return null;
        }

        //get timeslots & user
        foreach ($this->demoTimeslots as $slot) {
            if ($slot->app_id == $id){
                foreach ($this->demoParticipants as $participant){
                    if (in_array($slot->slot_id, $participant->slot_ids)){
                        $result->participants[] = $participant;
                    }
                }
                $result->timeslots[] = $slot;
            }
        }

        //get comments
        foreach ($this->demoComments as $comment){
            if ($comment->app_id = $result->app_id){
                $result->comments[] = $comment;
            }
        }

        return $result;
    }

    public function addTimeslot($app_id, $start, $end){
        $new_slot = new Timeslot(10, $app_id, $start, $end);

        foreach ($this->demoApp as $app){
            if ($app->app_id == $app_id){
                $app->timeslots[] = $new_slot;
            }
        }
    }

    /**
     * @param string $title
     * @param string $creator
     * @param string $description
     * @param string $location
     * @param string $expiration_date
     * @return bool
     */
    public function addNewAppointment(string $title, string $creator, string $description, string $location, int $creation_date, string $expiration_date): bool
    {
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
    }

    /**
     * @param int $app_id
     * @param array $slot_ids
     * @param $username
     * @return bool
     */
    public function addVotes(int $app_id, array $slot_ids, $username): bool
    {
        $appointment = $this->getAppointmentById($app_id);
        if (!$appointment){
            return false;
        }

        $appointment->participants[] = new Participant($slot_ids, $username);
        return true;

    }

    public function getCommentsByAppId($param)
    {
        $result = null;
        foreach ($this->demoApp as $val) {
            if ($val->app_id == $param) {
                $result = $val;
            }
        }

        $comments = array();

        foreach ($this->demoComments as $comment){
            if ($comment->app_id = $result->app_id){
                $comments[] = $comment;
            }
        }
        return $comments;
    }
}
