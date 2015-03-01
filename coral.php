<?php

class CoralShop{
private $corals = array(
        "Platygyra" => array("price" => "$80.00")

);

public function init(){

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$routes = explode('/', $this->paths($uri));
array_shift($routes);
$res = array_shift($routes);

if($res == 'corals'){
        $name = (array_shift($routes));

        if(empty($name)){
                $this->handle_base($method);
        }else{
                $this->handle_name($method,$name);
        }

}else{
        header('HTTP/1.1 404 Not Found');
}

}
private function handle_base($method){
        switch($method){
                case 'GET':
                        $this->result();
                        break;
                default:
                        header('HTTP/1.1 405 Method Not Allowed');
                        header('Allow: GET');
                        break;
        }
}
private function handle_name($method,$name){
        switch($method){
                case 'PUT':
                        $this->addCoral($name);
                        break;
                case 'DELETE':
                        $this->deleteCoral($name);
                        break;
                case 'GET':
                        $this->displayCoral($name);
                        break;
                default:
                        header('HTTP/1.1 405 Method Not Allowed');
                        header('Allow: GET, PUT, DELETE');
                        break;
        }
}

private function addCoral($name){
        if(isset($this->$corals[$name])){
                header('HTTP/1.1 409 Conflict');
                return;
        }
        $input = json_decode(file_get_contents('php://input'));
        if(is_null($input)){
                header('HTTP/1.1 400 Bad Request');
                $this->result();
                return;
        }
        $this->corals[$name] = $input;
        $this->result();
}

private function deleteCoral($name){
        if(isset($this->corals[$name])){
                unset($this->contacts[$name]);
                $this->result();
        }
        else{
                header('HTTP/1.1 404 Not Found');
        }
}

private function displayCoral($name){
        if(array_key_exists($name, $this->corals)){
                echo json_encode($this->corals[$name]);
                echo "\r\n";

        }
        else{
                header('HTTP/1.1 404 Not Found');
        }
}

private function paths($url){
        $uri = parse_url($url);
        return $uri['path'];
}

private function result(){
        header('Content-type: application/json');
        echo json_encode($this->corals);
        echo "\r\n";
}

}

$coralShop = new CoralShop;
$coralShop->init();
?>
       
