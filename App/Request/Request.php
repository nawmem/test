<?php


namespace App\Request;


class Request{

    private static $request_all = [];

    function __construct(){
        foreach ($_REQUEST as $req_key => $req_value){
            self::$request_all[$req_key] = $req_value;
            $this->$req_key = $req_value;
        }
    }

    public function all(){
        return self::$request_all;
    }

}