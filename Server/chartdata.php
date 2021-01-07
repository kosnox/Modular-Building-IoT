<?php
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
require("rb-mysql.php");

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
                      $agg = "COUNT";
                      break;
                default:
                  echo json_encode(["error" => "Nie podano agg"]);
                  http_response_code(400); 
                    break;
            }

            switch ($_GET["interval"]) {
              case "hournow":
                  $sql = "SELECT IF(MINUTE(_date) > 10, MINUTE(_DATE) DIV 10 * 10,0) AS minute, $agg(_data) AS data FROM devicedata WHERE device_id = :device_id AND date(now()) = date(_date) AND hour(now()) = hour(_date) GROUP BY minute ORDER BY minute";
                break;
              case "hourbefore":
                  $sql = "SELECT IF(MINUTE(_date) > 10, MINUTE(_DATE) DIV 10 * 10,0) AS minute, $agg(_data) AS data FROM devicedata WHERE device_id = :device_id AND IF(hour(now()) = 0,date(now() - INTERVAL 1 DAY),date(now())) = date(_date) AND hour(now() - INTERVAL 1 HOUR)  = hour(_date) GROUP BY minute ORDER BY minute";
                break;
                case "today":
                  $sql = "SELECT HOUR(_date) AS hour, $agg(_data) AS data FROM devicedata WHERE device_id = :device_id AND _date > NOW() - INTERVAL 1 DAY GROUP BY hour ORDER BY hour";
                break;
                case "yesterday":
                  $sql = "SELECT HOUR(_date) AS hour, $agg(_data) AS data FROM devicedata WHERE device_id = :device_id AND _date > NOW() - INTERVAL 2 DAY AND _date < NOW() - INTERVAL 1 DAY GROUP BY hour ORDER BY hour";
                break;
                case "7days":
                  $sql = "SELECT DATE(_date) AS date, $agg(_data) AS data FROM devicedata WHERE device_id = :device_id AND _date > NOW() - INTERVAL 6 DAY GROUP BY date ORDER BY date";
                break;
                case "30days":
                  $sql = "SELECT DATE(_date) AS date, $agg(_data) AS data FROM devicedata WHERE device_id = :device_id AND _date > NOW() - INTERVAL 29 DAY GROUP BY date ORDER BY date";
                break;
                case "year":
                  $sql = "SELECT MONTH(_date) AS month, $agg(_data) AS data FROM devicedata WHERE device_id = :device_id AND _date > NOW() - INTERVAL 1 YEAR GROUP BY month ORDER BY month";
                break;
              default:
                echo json_encode(["error" => "Nie podano interval"]);
                http_response_code(400); 
                  break;
          }
          $value = R::GetAll( $sql,
          [ ':device_id' => $device->id ]
      );

      $chartInfo = R::getRow( "SELECT * FROM devicereport WHERE device_id = :device_id",
      [ ':device_id' => $device->id ] );
  
        echo json_encode(Array("info" => $chartInfo,"data" => $value));
        http_response_code(200);
    }
    else{
      echo json_encode(["error" => "Złe urządzenie"]);
      http_response_code(400); //bad request
    }

}
else{
  echo json_encode(["error" => var_dump($_POST)]);
  http_response_code(400); //bad request
}
 

?>
