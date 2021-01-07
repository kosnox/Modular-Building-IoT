<?php
require("rb-mysql.php");

if (!empty($_GET["name"])) {
  require 'db.php';
  R::setup(dbCredentials());

  //pobierz obiekt urzadzenia
  $device = R::findOne( 'device', ' _floor = :floor AND _name = :name ', [ ":floor" => $_GET["floor"], ":name" => $_GET["name"] ]);

  //jezeli urzadzenie nie istnieje na pietrze to je utworz

  if($device != NULL){
    if(!empty($_GET["measurement"])){
      $chart= R::dispense( 'devicechart' );
      $chart->title = $_GET["title"];
      $chart->measurement = $_GET["measurement"];
      $chart->agg_fun = $_GET["agg_fun"];

      //przypisz dane do urzadzenia
      $device->ownDevicechartList[] = $chart;
    }

    $id = R::store( $device );
    if($id > 0){
      echo "<p style='color:green'>Zapisano pomyślnie</p>";
    }
      else{
        echo "<p style='color:red'>Błąd podczas zapisywania</p>";
      }
  }
  else{
    echo "<p style='color:red'>Urządzenie nie istnieje</p>";
  }
  
}
?>
<body>
<a href="add_device.php">Dodaj urządzenie | </a>
<?php for($i=1; $i <= 5; $i++){?>
  <a href="show.php?floor=<?=$i?>">Pokaż [<?=$i?>] | </a>
  <a href="change_settings.php?floor=<?=$i?>">Edytuj [<?=$i?>] |</a>
<?php }  ?>
<p>Urządzenie ustawiające: </p>
<form action="add_chart.php" method="get">
  <label for="name">Identyfikator urządzenia:</label><br>
   <input type="text" name="name"><br>
   <label for="floor">Piętro:</label><br>
    <input type="number" name="floor"><br>
    <label for="title">Tytuł:</label><br>
    <input type="text" name="title"><br>
    <label for="measurement">Pomiar:</label><br>
    <input type="text" name="measurement"><br>
   <label for="agg_fun">Typ funkcji agergującej:</label><br>
   <select name="agg_fun">
      <option value="avg">Średnia</option>
      <option value="sum">Suma</option>
      <option value="count">Suma</option>
  </select>
  <br />
   <input type="submit" value="Zapisz">
</form>
</body>
