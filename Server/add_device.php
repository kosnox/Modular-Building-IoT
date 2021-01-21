<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
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
#emaildiv{
  display:none;
}
</style>
</head>
<?php
require("rb-mysql.php");

if (!empty($_GET["name"])) {
  require 'db.php';
  R::setup(dbCredentials());
  //pobierz obiekt urzadzenia
  $device = R::findOne( 'device', ' _floor = :floor AND _name = :name ', [ ":floor" => $_GET["floor"], ":name" => $_GET["name"] ]);

  //jezeli urzadzenie nie istnieje na pietrze to je utworz
  if($device == NULL){

    $device= R::dispense( 'device' );
    $device->Name = $_GET["name"];
    $device->Floor = $_GET["floor"];

    if(!empty($_GET["type"])){
      $setting= R::dispense( 'devicesettings' );
      $setting->type = $_GET["type"];
      //jezeli podano wartosc numeryczna
      if(!empty($_GET["value"])){
          $setting->value = $_GET["value"];
      }
      else{
        //jezeli podano wartosc on/off
        $setting->value = isset($_GET["valueS"]) ? 1 : 0;
      }

      //przypisz dane do urzadzenia
      $device->ownDevicesettingsList[] = $setting;
    }

    if(!empty($_GET["measurement"])){
      $exclude = "";
      $report = R::dispense( 'devicereport' );
      $report->measurement = $_GET["measurement"];
      if(!isset($_GET["avg"])) $exclude .= "avg;";
      if(!isset($_GET["count"])) $exclude .= "count;";
      if(!isset($_GET["max"])) $exclude .= "max;";
      if(!isset($_GET["min"])) $exclude .= "min;";

      if(!empty($exclude)) $report->exclude = $exclude;

      if(isset($_GET["chart"])){
      $report->title = $_GET["title"];
      $report->agg_fun = $_GET["agg_fun"];
      }
     //przypisz dane do urzadzenia
      $device->ownDevicereportList[] = $report;
    }

    if(!empty($_GET["emailcheck"])){
      echo "here";
      $email = R::dispense( 'deviceemail' );
      $email->body = $_GET["body"];
      $email->subject = $_GET["subject"];
      $email->value = $_GET["comparevalue"];
      $email->compare = $_GET["comparesign"];
      $device->ownDeviceemailList[] = $email;
    }
    
    $id = R::store( $device );
    if($id > 0){
      $error=null;
    }
      else{
        $error = "Błąd podczas zapisywania";
      }
  }
  else{
    $error = "Urządzenie już istnieje";
  }
}

?>
<body>
<?php require("menu.php"); 
if(isset($_GET['name'])){
  if($error == null) echo "<div class='alert alert-success' role='alert'>Zapisano urządzenie pomyślnie</div>";
  else echo "<div class='alert alert-danger' role='alert'>$error</div>";
}
?>

<div class="container" style="margin-left:10px;">
<h2>Urządzenie ustawiające: </h2>
<form action="add_device.php" method="get">
  <label class="h6" for="name">Identyfikator urządzenia:</label><br>
   <input type="text" class="form-control" name="name"><br>
   <label class="h6" for="floor">Piętro:</label><br>
    <input type="number" class="form-control" name="floor"><br>
   <label class="h6" for="type">Typ ustawiania:</label><br>
   <input type="radio" name="type" class="form-check-inline" id="switcherino" value="switch" onclick="showSwitch()" checked>On/off</br>
   <input type="radio" name="type" class="form-check-inline" id="numberino" value="number" onclick="showNumber()">Liczba</br>
   <div id="switch">
     <label class="h6" for="ifswitch">Podaj wartość początkową:</label><br>
     <label class="switch" >
          <input type="checkbox" name="valueS" class="default">
          <span class="slider"></span>
        </label>
   </div>
   <div id="number" style="display:none;">
     <label class="h6" for="number">Podaj wartość początkową:</label><br>
     <input type="number" class="form-control" name="value" value="0"></br>
   </div>
   <input type="submit" class="btn btn-primary" value="Zapisz">
</form>
<!-- Urządzenie przesyłające dane -->
<hr/>
<h2>Urządzenie przesyłające dane: </h2>
<form action="add_device.php" method="get">
  <label class="h6" for="name">Identyfikator urządzenia:</label><br>
   <input type="text" class="form-control"  name="name"><br>
  <label class="h6" for="floor">Piętro:</label><br>
    <input type="number" class="form-control" name="floor"><br>
  <label class="h6" for="measurement">Pomiar:</label><br>
    <input type="text" class="form-control" name="measurement"><br>
    <!-- raport -->
    <label class="h6" for="floor">Dane do raportu:</label><br>
      <div>
        <input type="checkbox" name="count" checked>
        <label for="count">Zliczanie elementów</label>
      </div>
      <div>
        <input type="checkbox" name="avg" checked>
        <label for="avg">Średnia</label>
      </div>
      <div>
        <input type="checkbox" name="min" checked>
        <label for="min">Minimum</label>
      </div>
      <div>
        <input type="checkbox" name="max" checked>
        <label for="max">Maksimum</label>
      </div>
    <!-- wykres -->
    <label class="h6" for="Wykres">Wykres:</label><br>
    <input type="checkbox" id="chartcheck" name="chart"  onclick="showChartDiv()" checked>
    <label for="chart">Umieścić wykres</label>

    <div id="chartdiv">
      <label for="title">Tytuł:</label><br>
      <input type="text" class="form-control" name="title"><br>
      
      <label for="agg_fun">Typ funkcji agergującej:</label><br>
      <select class="form-control" name="agg_fun">
        <option value="avg">Średnia</option>
        <option value="sum">Suma</option>
        <option value="count">Zliczanie</option>
        <option value="count01">Zliczanie on/off</option>
      </select>
  </div></br>
  <!-- email -->
  <label class="h6" for="Wykres">Email:</label><br>
    <input type="checkbox" id="emailcheck" name="emailcheck"  onclick="showEmailDiv()">
    <label for="emailcheck">Wyślij email</label>

    <div id="emaildiv">
      <label for="subject">Temat:</label><br>
      <input type="text" class="form-control" name="subject"><br>
      <label for="body">Treść:</label><br>
      <input type="text" class="form-control" name="body"><br>
      <label for="comparevalue">Wartość do porównania:</label><br>
      <input type="text" class="form-control" name="comparevalue"><br>
      <label for="comparesign">Wyślij email gdy przesłana wartość jest:</label><br>
      <select class="form-control" name="comparesign">
        <option value="equal">Równa</option>
        <option value="notequal" >Nie równa</option>
        <option value="more">Większa</option>
        <option value="less">Mniejsza</option>
      </select>
  </div>
</br>
   <input type="submit" class="btn btn-primary" value="Zapisz">
</form>
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
