<?php

namespace App;

class App {

    static protected $user;
    static protected $isTest = 0;

    static public function set_user($user)
    {
        self::$user = $user;
    }

    static public function get_user()
    {
        return self::$user;
    }

    static public function set_test(){
        self::$isTest = 1;
    }

    static public function unset_test(){
        self::$isTest = 0;
    }

    static public function isTest() {
        return self::$isTest;
    }
}