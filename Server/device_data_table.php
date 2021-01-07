<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
  $('#dtBasicExample').dataTable( {
  "searching": false
} );
  $('.dataTables_length').addClass('bs-select');
});
</script>
</head>

<?php
require("rb-mysql.php");

if (!empty($_GET["name"]) && !empty($_GET["floor"]) ) {
  require 'db.php';
  R::setup(dbCredentials());

  //pobierz obiekt urzadzenia
  $device = R::findOne( 'device', ' _floor = :floor AND _name = :name ', [ ":floor" => $_GET["floor"], ":name" => $_GET["name"] ]);

  //jezeli urzadzenie nie istnieje na pietrze to je utworz

  if($device != NULL){
   
    $data = R::getAll( "SELECT * FROM devicedata WHERE device_id = :device_id",
    [ ':device_id' => $device->id ] );
    $counter = 1;
  }
  else{
    echo "<p style='color:red'>Nie udało się pobrać danych dla urządzenia!</p>";
  } 
}
else{
  echo "<p style='color:red'>Brak wymaganych danych!</p>";
  die();
}
?>
<body>
<?php require("menu.php"); ?>
<h2>Dane z urządzenia <?= $device["_name"] ?></h2>
<div width="50%">
<table id="dtBasicExample" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
  <thead>
    <tr>
    <th class="th-sm">#</th>
    <th class="th-sm">Dane</th>
    <th class="th-sm">Data</th>
    </tr>
<tbody>

<?php foreach($data as $d): ?>
<tr>
  <td><?=$counter++?></td>
  <td><?=$d["_data"]?></td>
  <td><?=$d["_date"]?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

</body>
