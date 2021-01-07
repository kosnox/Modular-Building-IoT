<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
<style>


/* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

/* Hide default HTML checkbox */
.switch input {display:none;}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input.default:checked + .slider {
  background-color: #444;
}
input.primary:checked + .slider {
  background-color: #2196F3;
}
input.success:checked + .slider {
  background-color: #8bc34a;
}
input.info:checked + .slider {
  background-color: #3de0f5;
}
input.warning:checked + .slider {
  background-color: #FFC107;
}
input.danger:checked + .slider {
  background-color: #f44336;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
.form-control{
  width:200px;
}
</style>
</head>
<?php
require("rb-mysql.php");

require 'db.php';
R::setup(dbCredentials());

if (isset($_GET["id"])) {

  $device = R::findOne( 'device', ' id = :device_id ', [ ":device_id" => $_GET["id"] ]);
  $device->_name = $_GET["name"];
  $device->_floor = $_GET["floor"];

  if($device != NULL){
    if(!empty($_GET["type"])){
      $setting = R::findOne( 'devicesettings', ' device_id = :device_id ', [ ":device_id" => $device->id ]);
      $setting->type = $_GET["type"];
      //jezeli podano wartosc numeryczna
      if($_GET["type"] == "number"){
          $setting->value = $_GET["value"];
      }
      else{
        //jezeli podano wartosc on/off
        $setting->value = isset($_GET["valueS"]) ? 1 : 0;
      }

      //przypisz dane do urzadzenia
      $id = R::store( $device );
      $id2 = R::store( $setting );
    }

    if(!empty($_GET["floor"]) && !empty($_GET["name"])){
      $exclude = "";
      $report = R::findOne( 'devicereport', ' device_id = :device_id ', [ ":device_id" => $device->id ]);

      $report->measurement = $_GET["measurement"];
      if(!isset($_GET["avg"])) { $exclude .= "avg;"; }
      if(!isset($_GET["count"])){ $exclude .= "count;"; }
      if(!isset($_GET["max"])){ $exclude .= "max;";}
      if(!isset($_GET["min"])){ $exclude .= "min;"; }
      
      $report->exclude = $exclude;

      if(isset($_GET["chart"])){
      $report->title = $_GET["title"];
      $report->agg_fun = $_GET["agg_fun"];
      }else{
        $report->title = null;
        $report->agg_fun = null;
      }

      $email = R::findOne( 'deviceemail', ' device_id = :device_id ', [ ":device_id" =>  $device->id ]);
      $wasNull = false;
      if(!empty($_GET["emailcheck"])){
        if($email == null){
          $wasNull = true;
          $email = R::dispense( 'deviceemail' );
        }

        $email->body = $_GET["body"];
        $email->subject = $_GET["subject"];
        $email->value = $_GET["comparevalue"];
        $email->compare = $_GET["comparesign"];
        if($wasNull){
          $device->ownDeviceemailList[] = $email;
        }
      }
      else{
        if($email != null)
          R::exec('DELETE FROM deviceemail WHERE device_id=:device_id', [ ":device_id" => $device->id]);
      }

     //przypisz dane do urzadzenia
     $id = R::store( $device );
     $id2 = R::store( $report );
     if($email != null)
        $id3 = R::store( $email );
    }
    
    if($id > 0 && $id2 > 0){
      $error = null;
    }
      else{
        $error = "";
        if($id < 0){
          $error .= "Błąd podczas zapisywania\n";
        }
        if($id < 0){
          $error .= "Nie podano pola pomiar\n";
        }
      }
  }
  else{
   $error = "Nie ma takiego urządzenia!";
  }
}

if(!empty($_GET["name"]) && !empty($_GET["floor"])){
  $device = R::findOne( 'device', ' _floor = :floor AND _name = :name ', [ ":floor" => $_GET["floor"], ":name" => $_GET["name"] ]);
  $fields = R::findOne( 'devicereport', ' device_id = :device_id ', [ ":device_id" => $device["id"] ]);
  $email = R::findOne( 'deviceemail', ' device_id = :device_id ', [ ":device_id" => $device["id"] ]);
  if($fields == null)
    $fields = R::findOne( 'devicesettings', ' device_id = :device_id ', [ ":device_id" => $device["id"] ]);
    
  if($device == null){
    require("menu.php"); 
    echo "<div class='alert alert-danger' role='alert'>Nie podano urządzenia do edycji!</div>";
    die();
  }   
}else{
  $error = "Nie podano wszystkich danych";
}

?>
<body>
<?php 
require("menu.php"); 
if(isset($_GET['id'])){
  if($error == null) echo "<div class='alert alert-success' role='alert'>Zapisano zmiany pomyślnie</div>";
  else echo "<div class='alert alert-danger' role='alert'>$error</div>";
}
?>

<div class="container" style="margin-left:10px;">
<?php if(isset($fields->type)): ?>
<h2>Urządzenie ustawiające: </h2>
<form action="edit_device.php" method="get">
  <label class="h6" for="name">Identyfikator urządzenia:</label><br>
   <input type="text" class="form-control" name="name" value="<?=$device->_name?>"><br>
   <label class="h6" for="floor">Piętro:</label><br>
    <input type="number" class="form-control" name="floor" value="<?=$device->_floor?>"><br>
   <label class="h6" for="type">Typ ustawiania:</label><br>
   <input type="radio" name="type" class="form-check-inline" id="switcherino" value="switch" onclick="showSwitch()" <?php if($fields->type=="switch") echo "checked" ?>>On/off</br>
   <input type="radio" name="type" class="form-check-inline" id="numberino" value="number" onclick="showNumber()" <?php if($fields->type=="number") echo "checked" ?>>Liczba</br>
   <div id="switch" <?php if($fields->type!="switch") echo "style='display:none;'" ?>>
     <label class="h6" for="ifswitch">Podaj wartość początkową:</label><br>
     <label class="switch" >
          <input type="checkbox" name="valueS" class="default" <?php if($fields->value == 1) echo"checked" ?>>
          <span class="slider"></span>
        </label>
   </div>
   <div id="number" <?php if($fields->type!="number") echo "style='display:none;'" ?>>
     <label class="h6" for="number">Podaj wartość początkową:</label><br>
     <input type="number" class="form-control" name="value" value="<?=$fields->value?>"></br>
   </div>
   <input type="hidden" name="id" value=<?=$device->id ?> />
   <input type="submit" class="btn btn-primary" value="Zapisz">
</form>
<?php endif; ?>
<!-- Urządzenie przesyłające dane -->
<?php if(isset($fields->measurement)): ?>
<h2>Urządzenie przesyłające dane: </h2>
<form action="edit_device.php" method="get">
  <label class="h6" for="name">Identyfikator urządzenia:</label><br>
   <input type="text" class="form-control"  name="name" value="<?=$device->_name?>"><br>
  <label class="h6" for="floor">Piętro:</label><br>
    <input type="number" class="form-control" name="floor" value="<?=$device->_floor?>"><br>
  <label class="h6" for="measurement">Pomiar:</label><br>
    <input type="text" class="form-control" name="measurement" value="<?=$fields->measurement?>"><br>
    <!-- raport -->
    <label class="h6" for="count">Dane do raportu:</label><br>
      <div>
        <input type="checkbox" name="count" <?php if (strpos($fields->exclude, 'count') === false) echo("checked"); ?>>
        <label for="count">Zliczanie elementów</label>
      </div>
      <div>
        <input type="checkbox" name="avg" <?php if (strpos($fields->exclude, 'avg') === false) echo("checked");?>>
        <label for="avg">Średnia</label>
      </div>
      <div>
        <input type="checkbox" name="min" <?php if (strpos($fields->exclude, 'min') === false) echo("checked");?>>
        <label for="min">Minimum</label>
      </div>
      <div>
        <input type="checkbox" name="max" <?php if (strpos($fields->exclude, 'max') === false) echo("checked");?>>
        <label for="max">Maksimum</label>
      </div>
    <!-- wykres -->
    <label class="h6" for="Wykres">Wykres:</label><br>
    <input type="checkbox" id="chartcheck" name="chart"  onclick="showChartDiv()" <?php if (!empty($fields->agg_fun)) echo("checked");?>>
    <label for="chart">Umieścić wykres</label>

    <div id="chartdiv" <?php if(empty($fields->agg_fun)) echo("style='display:none'");?>>
      <label for="title">Tytuł:</label><br>
      <input type="text" class="form-control" name="title" value="<?=$fields->title?>"><br>
      
      <label for="agg_fun">Typ funkcji agergującej:</label><br>
      <select class="form-control" name="agg_fun">
        <option value="avg" <?php if ($fields->agg_fun == "avg") echo("selected");?>>Średnia</option>
        <option value="sum" <?php if ($fields->agg_fun == "sum") echo("selected");?>>Suma</option>
        <option value="count" <?php if ($fields->agg_fun == "count") echo("selected");?>>Zliczanie</option>
      </select>
  </div>
</br>
  <!-- email -->
  <label class="h6" for="Wykres">Email:</label><br>
    <input type="checkbox" id="emailcheck" name="emailcheck" <?php if (!empty($email->body)) echo("checked");?>  onclick="showEmailDiv()">
    <label for="emailcheck">Wyślij email</label>

    <div id="emaildiv"<?php if(empty($email->body)) echo("style='display:none'"); ?>>
      <label for="subject">Temat:</label><br>
      <input type="text" class="form-control" name="subject"  value="<?=$email != null ? $email->subject : "" ?>"><br>
      <label for="body">Treść:</label><br>
      <input type="text" class="form-control" name="body" value="<?=$email != null ? $email->body : "" ?>"><br>
      <label for="comparevalue">Wartość do porównania:</label><br>
      <input type="text" class="form-control" name="comparevalue"  value="<?=$email != null ? $email->value : "" ?>"><br>
      <label for="comparesign">Wyślij email gdy przesłana wartość jest:</label><br>
      <select class="form-control" name="comparesign">
        <option value="equal" <?php if ($email != null && $email->compare == "equal") echo("selected");?>>Równa</option>
        <option value="notequal" <?php if ($email != null &&  $email->compare == "notequal") echo("selected");?>>Nie równa</option>
        <option value="more" <?php if ($email != null &&  $email->compare == "more") echo("selected");?>>Większa</option>
        <option value="less" <?php if ($email != null &&  $email->compare == "less") echo("selected");?>>Mniejsza</option>
      </select>
  </div>
</br>
<input type="hidden" name="id" value=<?=$device->id ?> />
<input type="submit" class="btn btn-primary" value="Zapisz">

</form>

<?php endif;?>
</div>

<script>

var ifswitch = document.getElementById("switch");
var ifnumber = document.getElementById("number");
var chartDiv = document.getElementById("chartdiv");
var chartCheck = document.getElementById("chartcheck");
var emailDiv = document.getElementById("emaildiv");
var emailCheck = document.getElementById("emailcheck");
function showSwitch() {
  if(ifswitch.style.display == "none"){
    ifswitch.style.display =  "block";
    ifnumber.style.display = "none";
  }
}

function showNumber() {
  if(ifnumber.style.display == "none"){
    ifnumber.style.display =  "block";
    ifswitch.style.display = "none";
  }
}

function showChartDiv() {
  if(chartCheck.checked == true)
      chartDiv.style.display =  "block";
    else
    chartDiv.style.display =  "none";
}
function showEmailDiv() {
  if(emailCheck.checked == true)
    emailDiv.style.display =  "block";
    else
    emailDiv.style.display =  "none";
}


</script>
</body>
