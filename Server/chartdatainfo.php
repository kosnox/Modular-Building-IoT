<?php
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
require("rb-mysql.php");

if (isset($_GET["floor"]) && isset($_GET["name"]) ) {
  require 'db.php';
  R::setup(dbCredentials());

    //pobierz obiekt urzadzenia
    $device = R::findOne( 'device', ' _floor = :floor AND _name = :name ',
              [ ":floor" => $_GET["floor"], ":name" => $_GET["name"] ]);


  if(!empty($device)){
    $chartInfo = R::getRow( "SELECT * FROM devicechart WHERE device_id = :device_id",
    [ ':device_id' => $device->id ] );

    if(!empty($chartInfo)){
      echo json_encode($chartInfo);
      http_response_code(200);
    }
    else{
      echo json_encode(["status" => "Nic nie pobrano informacji o wykresie"]);
      http_response_code(400); //bad request
    }
  }
  else{
    echo json_encode(["status" => "Podano złe urządzenie"]);
    http_response_code(400); //bad request
  }
}
else{
  echo json_encode(["status" => "Nie podano wszystkich danych"]);
  http_response_code(400); //bad request
}
?>
