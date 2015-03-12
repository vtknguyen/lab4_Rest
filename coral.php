<?php
class CoralShop{

        public function __construct(){
                $this->dbConn();
        }
        private function dbConn(){
                require_once('dbConn.php');
                $this->conn = new mysqli($dbHost, $dbUser, $dbPass, $db);
                if ($this->conn->connect_error) {
                        die('Connect Error: ' . $this->conn->connect_error);
                }
        }

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
                $stmt = $this->conn->prepare("SELECT name FROM corals WHERE name=?");
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $stmt->bind_result($result['name']);
                $stmt->fetch();
                $stmt->close();
                if($result['name']){
                        header('HTTP/1.1 409 Conflict');
                        return;
                }
                $input = json_decode(file_get_contents('php://input'));
                if(is_null($input->price)){
                        header('HTTP/1.1 400 Bad Request');
                        $this->result();
                        return;
                }
                $price = $input->price;
                $stmt = $this->conn->prepare("INSERT INTO corals (name, price) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $price);
                $stmt->execute();
                $this->result();
        }

        private function deleteCoral($name){
                $stmt = $this->conn->prepare("SELECT name FROM corals WHERE name=?");
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $stmt->bind_result($result['name']);
                $stmt->fetch();
                $stmt->close();
                if($result['name']){
                        $stmt = $this->conn->prepare("DELETE FROM corals WHERE name=?");
                        $stmt->bind_param("s", $name);
                        $stmt->execute();
                        $this->result();
                }
                else{
                        header('HTTP/1.1 404 Not Found');
                }
        }
        private function displayCoral($name){
                $stmt = $this->conn->prepare("SELECT name,price FROM corals WHERE name=?");
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $stmt->bind_result($result['name'], $result['price']);
                $stmt->fetch();
                if($result['name']){
                        header('Content-type: application/json');
                        echo json_encode($result)."\r\n";

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
                $stmt = "SELECT name, price FROM corals";
                $data = $this->conn->query($stmt);
                $results = $data->fetch_all(MYSQLI_ASSOC);
                echo json_encode($results)."\r\n";
        }

}

$coralShop = new CoralShop;
$coralShop->init();
?>
