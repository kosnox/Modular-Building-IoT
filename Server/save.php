<?php
header('Access-Control-Allow-Origin: *');
require("rb-mysql.php");
require("sendEmail.php");
if (!empty($_GET["floor"])) {
  require 'db.php';
  R::setup(dbCredentials());
  
  $floor = $_GET["floor"];
  unset($_GET["floor"]);

  foreach ($_GET as $key => $value) {

      //pobierz obiekt urzadzenia
      $device = R::findOne( 'device', ' _floor = :floor AND _name = :name ', [ ":floor" => $floor, ":name" => $key ]);

      //jezeli urzadzenie nie istnieje na pietrze to je utworz
      if(is_null($device)){

        $device= R::dispense( 'device' );
        $device->Name = $key;
        $device->Floor = $floor;
      }

      //zapisz dane z czujnikow
      $data= R::dispense( 'devicedata' );
      $data->Data = $value;
      $data->Date = R::isoDateTime();

      //przypisz dane do urzadzenia
      $device->ownDevicedataList[] = $data;
      $id = R::store( $device );
  }

  if($id > 0 ){
    $email = R::findOne( 'deviceemail', ' device_id = :device_id ', [ ":device_id" => $device->id]);
    if($email != null){
      if(compareValue($data->Data,$email->value,$email->compare)){
       sendEmail("uzytkownikiot@gmail.com",$email->subject,$email->body);
      }
    }

    echo json_encode(["status" => "ok"]);
    http_response_code(201); //created
  }
  else{
    echo json_encode(["status" => "error"]);
    http_response_code(400); //bad request
  }
}

function compareValue($value,$dbValue,$compare){
  switch($compare){
    case "equal":
      return $value == $dbValue;
    case "notequal":
        return $value != $dbValue;
    break;
    case "more":
      return $value > $dbValue;
    break;
    case "less":
      return $value < $dbValue;
    break;
    
  }
}

?>
