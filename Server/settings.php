<?php
header('Content-type: application/json');
require("rb-mysql.php");

if (!empty($_GET["floor"])) {
require 'db.php';
R::setup(dbCredentials());
  $floor = $_GET["floor"];
  unset($_GET["floor"]);

  $json = [];
  foreach ($_GET as $key => $value) {

    //pobierz obiekt urzadzenia
    $device = R::findOne( 'device', ' _floor = :floor AND _name = :name ',
              [ ":floor" => $floor, ":name" => $value ]);

    if(!is_null($device)){
      $value = R::getCell( 'SELECT value FROM devicesettings WHERE device_id = :deviceId',
          [ ':deviceId' => $device->id ]
      );

      if(!is_null($value)){
        $json[] = [
              'device' => $device->Name,
              'value' => $value
              ];
      }
    }

  }

  if($json != [])
    echo json_encode($json);
  else
      http_response_code(400); //bad request
}


?>
