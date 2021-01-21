<?php
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
require("rb-mysql.php");
//var_dump($_GET);
if (isset($_GET["floor"])) {
  require 'db.php';
  R::setup(dbCredentials());
  
  $agg = "";
  $sql = "";
    //pobierz obiekt urzadzenia
    $device = R::findOne( 'device', ' _floor = :floor AND _name = :name ',
              [ ":floor" => $_GET["floor"], ":name" => $_GET["name"] ]);
    if(!is_null($device)){
              switch ($_GET["agg"]) {
                case "avg":
                    $agg = "AVG";
                    break;
                case "sum":
                    $agg = "SUM";
                    break;
                    case "count":
                    case "count01":
                      $agg = "COUNT";
                      break;
                default:
                  echo json_encode(["error" => "Nie podano agg"]);
                  http_response_code(400); 
                    break;
            }
              if(isset($_GET["startdate"]) && isset($_GET["enddate"]) && isset($_GET["startdate2"]) && isset($_GET["enddate2"])){
                $sql = "SELECT IFNULL(dd.data, 0) AS data, d._date AS date from dates d LEFT JOIN (SELECT DATE(_date) as date, AVG(_data) as data FROM devicedata WHERE device_id =" .$device->id . " GROUP BY date) dd ON dd.date = d._date where d._date BETWEEN '" . $_GET["startdate"] .  "' AND '" . $_GET["enddate"] . "'";
                $sql2 = "SELECT IFNULL(dd.data, 0) AS data, d._date AS date from dates d LEFT JOIN (SELECT DATE(_date) as date, AVG(_data) as data FROM devicedata WHERE device_id =" .$device->id . " GROUP BY date) dd ON dd.date = d._date where d._date BETWEEN '" . $_GET["startdate2"] .  "' AND '" . $_GET["enddate2"] . "'";
              }else{
                echo json_encode(["error" => "Nie podano interval"]);
                http_response_code(400); 
              }
          
          $value = R::GetAll( $sql,
          [ ':device_id' => $device->id ]
      );

      $value2 = R::GetAll( $sql2,
      [ ':device_id' => $device->id ]
  );

      $chartInfo = R::getRow( "SELECT * FROM devicereport WHERE device_id = :device_id",
      [ ':device_id' => $device->id ] );
      R::close();
        echo json_encode(Array("info" => $chartInfo,"data" => $value,"data2" => $value2));
        //echo json_encode(Array("info" => $chartInfo,"data" => $value));
        http_response_code(200);
    }
    else{
      R::close();
      echo json_encode(["error" => "Złe urządzenie"]);
      http_response_code(400); //bad request
    }

}
else{
  echo json_encode(["error" => var_dump($_POST)]);
  http_response_code(400); //bad request
}
 

?>
