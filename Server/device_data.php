<?php
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
require("rb-mysql.php");

if (isset($_GET["floor"]) && isset($_GET["name"]) && isset($_GET["interval"])) {
  require 'db.php';
  R::setup(dbCredentials());
  
  $agg = "";
  $sql = "";
    //pobierz obiekt urzadzenia
    $device = R::findOne( 'device', ' _floor = :floor AND _name = :name ',
              [ ":floor" => $_GET["floor"], ":name" => $_GET["name"] ]);
    if(!is_null($device)){
            switch ($_GET["interval"]) {
              case "hournow":
                  $sql = "SELECT COUNT(*) AS 'count',MAX(_data + 0.0) AS 'max',MIN(_data + 0.0) AS 'min',TRUNCATE(AVG(_data + 0.0), 2) AS 'avg' FROM devicedata WHERE device_id = :device_id AND date(now()) = date(_date) AND hour(now()) = hour(_date)";
                break;
              case "hourbefore":
                  $sql = "SELECT COUNT(*) AS 'count',MAX(_data + 0.0) AS 'max',MIN(_data + 0.0) AS 'min',TRUNCATE(AVG(_data + 0.0), 2) AS 'avg' FROM devicedata WHERE device_id = :device_id AND IF(hour(now()) = 0,date(now() - INTERVAL 1 DAY),date(now())) = date(_date) AND hour(now() - INTERVAL 1 HOUR)  = hour(_date)";
                break;
                case "today":
                  $sql = "SELECT COUNT(*) AS 'count',MAX(_data + 0.0) AS 'max',MIN(_data + 0.0) AS 'min',TRUNCATE(AVG(_data + 0.0), 2) AS 'avg' FROM devicedata WHERE device_id = :device_id AND _date > NOW() - INTERVAL 1 DAY";
                break;
                case "yesterday":
                  $sql = "SELECT COUNT(*) AS 'count',MAX(_data + 0.0) AS 'max',MIN(_data + 0.0) AS 'min',TRUNCATE(AVG(_data + 0.0), 2) AS 'avg' FROM devicedata WHERE device_id = :device_id AND _date > NOW() - INTERVAL 2 DAY AND _date < NOW() - INTERVAL 1 DAY";
                break;
                case "7days":
                  $sql = "SELECT COUNT(*) AS 'count',MAX(_data + 0.0) AS 'max',MIN(_data + 0.0) AS 'min',TRUNCATE(AVG(_data + 0.0), 2) AS 'avg' FROM devicedata WHERE device_id = :device_id AND _date > NOW() - INTERVAL 6 DAY ";
                break;
                case "30days":
                  $sql = "SELECT COUNT(*) AS 'count',MAX(_data + 0.0) AS 'max',MIN(_data + 0.0) AS 'min',TRUNCATE(AVG(_data + 0.0), 2) AS 'avg' FROM devicedata WHERE device_id = :device_id AND _date > NOW() - INTERVAL 29 DAY";
                break;
                case "year":
                  $sql = "SELECT COUNT(*) AS 'count',MAX(_data + 0.0) AS 'max',MIN(_data + 0.0) AS 'min',TRUNCATE(AVG(_data + 0.0), 2) AS 'avg' FROM devicedata WHERE device_id = :device_id AND _date > NOW() - INTERVAL 1 YEAR";
                break;
              default:
                echo json_encode(["status" => "Nie podano interval"]);
                http_response_code(400); 
                  break;
          }
          $value = R::GetAll( $sql,
          [ ':device_id' => $device->id ]
      );
    }
  }
  if($value != []){
    if(!empty($value)){
      echo json_encode($value);
      http_response_code(200);
    }
    else{
      echo json_encode(["status" => "Nic nie pobrano informacji z urządzenia1"]);
      http_response_code(400); //bad request
    }
  }
  else{
    echo json_encode(["status" => "Nie niepobrano danych z urządzenia2"]);
    http_response_code(400); //bad request
  }
?>
