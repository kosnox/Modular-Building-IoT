<?php
header('Content-type: application/json');
require("rb-mysql.php");
require 'db.php';

if (!empty($_GET["floor"])) {
	R::setup(dbCredentials());
  $floor = $_GET["floor"];
  unset($_GET["floor"]);

  $json = [];
  foreach ($_GET as $key => $value) {
  //pobierz obiekt urzadzenia
  $device = R::findOne('device', ' _floor = :floor AND _name = :name ', [":floor" => $floor, ":name" => $key]);
  if ($device != NULL) {
      $setting = R::findOne('devicesettings', ' device_id = :device_id', [":device_id" => $device->id ]);
      $setting->value = $value;
 
      //przypisz dane do urzadzenia
      $device->ownDevicesettingsList[] = $setting;

      $id = R::store($device);

      if ($id > 0) {
        if($setting->save_data == 1){
          $device = R::findOne( 'device', ' _floor = :floor AND _name = :name ', [ ":floor" => $floor, ":name" => $key ]);

          //zapisz dane 
          $data= R::dispense( 'devicedata' );
          $data->Data = $value;
          $data->Date = R::isoDateTime();
    
          //przypisz dane do urzadzenia
          $device->ownDevicedataList[] = $data;
          $id = R::store( $device );
          http_response_code(200);
        }

      } else {
        echo http_response_code(400);
      }
    
  } else {
    http_response_code(400); //bad request
  }
}
}
else { 
      http_response_code(400); //bad request
}
?>
