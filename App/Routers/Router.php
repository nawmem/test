<?php


namespace App\Routers;


class Router
{
    // гет роуты
    private static $get_routers = [];
    // пост роуты
    private static $post_routers = [];

    //добавляем гет роут
    public static function routeGet($arr){
        array_push(self::$get_routers, $arr);
    }

    // добавляем пост роут
    public static function routePost($arr){
        array_push(self::$post_routers, $arr);
    }

    // метод который запускает роут по урлу
    public static function searhUri($req_uri, $method)
    {
        // нужно найти роут соответствующий прегматчу и от него плясать
        function get_param_url($self_routers, $get_uri){
            // массив контроллера и роута который в итоге выполним
            $array_controller = [
                'Controller' => '',
                'Route' => ''
            ];
            // массив динамических переменных что записаны в роуте
            $array_execte_url = [];
            // строка динамических переменных которые потом передадим в метод контроллера
            $str_execute_url = '';
            foreach ($self_routers as $get_rout){
                // разбиваем роут по слешу чтобы сравнить с УРИ
                $preg_split_route = array_values(array_filter(preg_split("/\//", $get_rout['Route']), function ($item_rout){
                    return $item_rout !== '';
                }));
                // разбиваем УРИ по слешу
                $preg_split_uri = array_values(array_filter(preg_split("/\//", $get_uri), function ($item_rout){
                    return $item_rout !== '';
                }));
                // cравниваем колличество значений в массиве разбитой строки и разбитого роута
                if (count($preg_split_uri) == 0){
                    if (strlen($get_rout['Route']) === 1 && $get_uri === '/'){
                        $array_controller['Controller'] = $get_rout['Controller'][0];
                        $array_controller['Route'] = $get_rout['Controller'][1];
                    }
                }elseif (count($preg_split_route) === count($preg_split_uri)) {
                    // пребираем массив роута
                    for ($i = 0; $i < count($preg_split_route); $i++) {
                        $item_rt = $preg_split_route[$i];
                        $item_ur = $preg_split_uri[$i];
                        // ищем есть ли переменная в строке роута которую указал юзер
                        if (preg_match('/\{:[a-zA-Z0-9]{1,}\}/',$item_rt, $sams)){
                            // запоминаем какой контроллер и роут найден
                            $array_controller['Controller'] = $get_rout['Controller'][0];
                            $array_controller['Route'] = $get_rout['Controller'][1];
                            $is_method = true;
                            // создаем строку перчисленных переменных чтобы потом передать в метод контроллера
                            $str_execute_url .= '$'.substr($sams[0],2,-1).' ,';
                            // записываем в массив названия (ключи) и значения
                            $array_execte_url[substr($sams[0],2,-1)] = $item_ur;
                        }elseif($get_rout['Route'] == $get_uri){
                            // если URL и роут совпали то он и нужен значить
                            $array_controller['Controller'] = $get_rout['Controller'][0];
                            $array_controller['Route'] = $get_rout['Controller'][1];
                        }
                    }
                }
            }
            $send_class = $array_controller['Controller'];
            $send_method = $array_controller['Route'];
            // убираем последнюю запятую из строки
            $str_execute_url = substr($str_execute_url, 0, -1);
            // создаем переменные из массива
            extract($array_execte_url);
            if (strlen($str_execute_url) > 0){
                // Выполняем стат метод с динамическими переменными считаными из роута
                // и выполняем код
                eval("return $send_class::$send_method($str_execute_url);");
            }else{
                eval("return $send_class::$send_method();");
            }
        }
        switch ($method){
            // выполняем ГЕТ роуты
            case 'GET':
                get_param_url(self::$get_routers, $req_uri);
                break;
            // выполняем ПОСТ роуты
            case 'POST':
                echo '<br>---POST---<br>';
                get_param_url(self::$post_routers, $req_uri);
                break;
            default:
                return '<br><br><br>Oops... ЧТо за метод? дай мне POST или GET?';
        }
    }
}