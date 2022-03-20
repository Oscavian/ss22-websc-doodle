<?php
class Person {
    public $id;
    public $firstname;
    public $lastname;
    public $email;
    public $phone;
    public $department;

    function __construct($id, $fn, $ln, $mail, $phone, $dept) {
        $this->id = $id;
        $this->firstname = $fn;
        $this->lastname=$ln;
        $this->email=$mail;
        $this->phone=$phone;
        $this->department=$dept;

      }
}
