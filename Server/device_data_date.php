<?php
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
require("rb-mysql.php");

if (isset($_GET["floor"]) && isset($_GET["name"])) {
  require 'db.php';
  R::setup(dbCredentials());
  
  $agg = "";
  $sql = "";
    //pobierz obiekt urzadzenia
    $device = R::findOne( 'device', ' _floor = :floor AND _name = :name ',
              [ ":floor" => $_GET["floor"], ":name" => $_GET["name"] ]);

    if(!is_null($device)){

      $sql = "SELECT COUNT(*) AS 'count',IFNULL(MAX(_data + 0.0),0) AS 'max',IFNULL(MIN(_data + 0.0),0) AS 'min',IFNULL(TRUNCATE(AVG(_data + 0.0), 2),0) AS 'avg' FROM devicedata WHERE device_id = :device_id AND _date BETWEEN '" . $_GET["startdate"] .  "' AND '" . $_GET["enddate"] . "'";
      $sql2 = "SELECT COUNT(*) AS 'count',IFNULL(MAX(_data + 0.0),0) AS 'max',IFNULL(MIN(_data + 0.0),0) AS 'min',IFNULL(TRUNCATE(AVG(_data + 0.0), 2),0) AS 'avg' FROM devicedata WHERE device_id = :device_id AND _date BETWEEN '" . $_GET["startdate2"] .  "' AND '" . $_GET["enddate2"] . "'";

          $value = R::GetAll( $sql,
          [ ':device_id' => $device->id ]
      );
      $value2 = R::GetAll( $sql2,
      [ ':device_id' => $device->id ]
    );
    }
  }
  if($value != []){
    if(!empty($value) && !empty($value2)){
      
      echo json_encode( array($value,$value2));
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
