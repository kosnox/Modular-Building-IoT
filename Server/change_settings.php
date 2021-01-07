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
</style>
</head>
<?php
require("rb-mysql.php");

require 'db.php';
R::setup(dbCredentials());

if (!isset($_GET["floor"])) die();

if (!empty($_GET["name"])) {

  //pobierz obiekt urzadzenia
  $device = R::findOne('device', ' _floor = :floor AND _name = :name ', [":floor" => $_GET["floor"], ":name" => $_GET["name"]]);
  if ($device != NULL) {

    if (isset($_GET["formsubmit"])) {
      $setting = R::findOne('devicesettings', ' device_id = :device_id', [":device_id" => $device->id ]);
      if($setting->type == "switch"){
        $setting->value = isset($_GET["value"]) ? 1 : 0;
      }else{
        $setting->value = $_GET["value"];
      }

      //przypisz dane do urzadzenia
      $device->ownDevicesettingsList[] = $setting;

      $id = R::store($device);

      if ($id > 0) {
        echo "<p style='color:green'>Zapisano pomyślnie</p>";
      } else {
        echo "<p style='color:red'>Błąd podczas zapisywania</p>";
      }
    } else {
      echo "<p style='color:red'>Nie podano wartości do zapisania</p>";
    }
  } else {
    echo "<p style='color:red'>Nie podano wszystkich danych</p>";
  }
}
$dev = R::getAll(
  'SELECT * FROM device INNER JOIN devicesettings ON device.id = device_id WHERE _floor = :floor',
  [':floor' => $_GET['floor']]
);
?>

<body>
<?php require("menu.php"); ?>
<div style="margin-left:10px;">
  <h2>Zmiana ustawień:</h2>

  <?php foreach ($dev as $d) : ?>
    <form action="change_settings.php" method="get">
      <label for="name" class="h6"><?= $d["_name"] ?></label><br>
      <input type="hidden" name="name" value="<?= $d["_name"] ?>" /> 
      <input type="hidden" name="floor" value="<?= $_GET["floor"] ?>" /> </br>
      <?php if ($d["type"] == "switch") : ?>
        <div id="switch">
        <label class="switch" >
          <input type="checkbox" name="value" class="default"  <?php if ($d["value"] == 1) echo "checked" ?>>
          <span class="slider"></span>
        </label>
        </div>
      <?php else : ?>
        <div id="number">
          <input type="number" class="form-control" style="width:150px" name="value" value="<?= $d["value"] ?>"></br>
        </div>
      <?php endif; ?>
      <input type="submit" name="formsubmit" class="btn btn-secondary btn-sm" value="Zapisz">
    </form>
    <hr />
  <?php endforeach; ?>
      </div>
</body>