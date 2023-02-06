<?php


namespace App\Controllers;


use App\Request\Request;
use Jenssegers\Agent\Agent;

class HomeController{

    public static function createHome(){
        include __DIR__ . '/../Viewer/home.html';
    }
    public static function createRegistration(){
        if ($_COOKIE['auth']){
            header('location: http://infa.site/dashboard');
        }
        include __DIR__ . '/../Viewer/reg.html';
        unset($_COOKIE['err_reg']);
    }
    public static function storeRegistration(){
        unset($_COOKIE['err_reg']);
        setcookie('auth', false);
        $request = new Request();
        if (!empty($request->login) && !empty($request->password)) {
            $login = $request->login;
            $pass = password_hash($request->password, PASSWORD_DEFAULT);
            $date = date('Y-m-d H:i:s');
            $sql = new \SQLite3(__DIR__ . '/../db/generate.db');
            $get_login = $sql->query("SELECT login FROM users WHERE login='$login'");
            if ($get_login->fetchArray(SQLITE3_ASSOC) != null) {
                setcookie('err_reg', 'Такой логин уже есть'. time()+3);
            } else {
                $result = $sql->prepare("INSERT INTO users ( login, create_at, password) VALUES (:login, :date, :pass)");
                $result->bindParam(':login', $login, SQLITE3_TEXT);
                $result->bindParam(':date', $date, SQLITE3_TEXT);
                $result->bindParam(':pass', $pass, SQLITE3_TEXT);
                $res = $result->execute();
                setcookie('auth', true, time()+3600);
                header('location: http://infa.site');
            }
        }
        header('location: http://infa.site/registration');

    }
    public static function createLogin(){
        if (isset($_COOKIE['auth'])){
            header('location: http://infa.site');
        }
        include __DIR__ . '/../Viewer/login.html';
        unset($_COOKIE['err_reg']);
    }
    public static function storeLogin(){
        unset($_COOKIE['err_reg']);
        setcookie('auth', false);
        $request = new Request();

        if (!empty($request->login) && !empty($request->password)){
            $login = $request->login;
            $sql = new \SQLite3(__DIR__.'/../db/generate.db');
            $get_login = $sql->query("SELECT * FROM users WHERE login='$login'");
            $pass = $get_login->fetchArray(SQLITE3_ASSOC)['password'];

            if (password_verify($request->password, $pass)){
                setcookie('auth', true, time()+3600);
                setcookie('user_id', true, time()+3600);
                setcookie('name', $login, time()+3600);
                header('location: http://infa.site/dashboard');
            }else{
                setcookie('err_reg', 'В общем ты ошибся в логинe или пароле ', time()+3);
            }
        }
        header('location: http://infa.site/login');
    }

    public static function storeGenerateUri(){
        $request = new Request();
        if (!empty($_REQUEST)){
            $random_uri = bin2hex(random_bytes(3));
            $user_id = isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : '';
            $date = date('Y-m-d H:i:s');
            $sql = new \SQLite3(__DIR__.'/../db/generate.db');
            $get_random_uri = $sql->query("SELECT * FROM uri WHERE uri_from='$random_uri'");
            $is_uri = $get_random_uri->fetchArray(SQLITE3_ASSOC);
            if($is_uri === false){
                $result = $sql->prepare("INSERT INTO uri ( uri_from, uri_to, user_id, create_at) VALUES (:from, :to,:user_id, :date)");
                $result->bindParam(':from', $random_uri, SQLITE3_TEXT);
                $result->bindParam(':user_id', $user_id, SQLITE3_TEXT);
                $result->bindParam(':to', $request->to_uri, SQLITE3_TEXT);
                $result->bindParam(':date', $date, SQLITE3_TEXT);
                $result->execute();
            }
            setcookie('to_uri', $request->to_uri, time() + 3);
            setcookie('from_uri', 'http://'.$_SERVER['HTTP_HOST'].'/ln/'.$random_uri , time() + 3);
            header('location: /');
        }
    }

    public static function createRedirectUri($url){
        $agent = new Agent();
        $agent->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        $get_link = new \SQLite3(__DIR__.'/../db/generate.db');
        $res_get_uri = $get_link->query("SELECT * FROM uri WHERE uri_from='$url'");
        $uri = $res_get_uri->fetchArray(SQLITE3_ASSOC);
        $get_link->close();
        $date = date('Y-m-d H:i:s');
        $device = $agent->device();
        $platform = $agent->platform();
        $browser = $agent->browser();
        if ($uri) {
            $sql = new \SQLite3(__DIR__.'/../db/generate.db');
            $result = $sql->prepare("INSERT INTO stat_uri ( uri_id, ip_address, device, platform, browser,  create_at) VALUES (:uri_id, :ip, :device, :platform, :browser, :date)");
            $result->bindParam(':uri_id', $uri['id'], SQLITE3_TEXT);
            $result->bindParam(':ip', $_SERVER['REMOTE_ADDR'], SQLITE3_TEXT);
            $result->bindParam(':device', $device, SQLITE3_TEXT);
            $result->bindParam(':platform', $platform, SQLITE3_TEXT);
            $result->bindParam(':browser', $browser, SQLITE3_TEXT);
            $result->bindParam(':date', $date, SQLITE3_TEXT);
            $res= $result->execute();
            $get_link->close();
            $uri_to = $uri['uri_to'];
            header("location: $uri_to");
        }else{
            header("location: /");
        }
    }

    public static function createLogout(){
        setcookie('auth', false, -1);
        setcookie('ser_id', false, -1);
        setcookie('name', false, -1);
        setcookie('array_uri', false, -1);
        header('location: http://infa.site/login');
    }

    public static function createDashboard(){
        if (isset($_COOKIE['auth'])){
            $user_id = (integer)$_COOKIE['user_id'];
            $sql = new \PDO('sqlite:/'.__DIR__.'/../db/generate.db');
            $result = $sql->query("SELECT * FROM uri WHERE user_id = ".$user_id);
            $array_uri = $result->fetchAll(2);
            $_SESSION['array_uri'] = $array_uri;
            include __DIR__ . '/../Viewer/dashboard.html';
        }else{
            header('location: '.$_SERVER['HTTP_REFERER'].'login');
        }
    }

    public static function createDashboardLinkInfo($id){

        if (isset($_COOKIE['auth'])){
            $sql = new \PDO('sqlite:/'.__DIR__.'/../db/generate.db');
            $result = $sql->query("SELECT * FROM stat_uri WHERE uri_id=".$id);
            $array_stat_uri = $result->fetchAll(2);
            $_SESSION['array_stat_uri'] = $array_stat_uri;
            include __DIR__.'/../Viewer/dashboard.html';
        }else{
            header('location: '.$_SERVER['HTTP_REFERER'].'login');
        }
    }
}