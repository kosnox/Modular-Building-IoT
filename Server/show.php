<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script>
function confirmDelete(url){
  if(confirm("Czy na pewno usunąć urządzenie?")){
    window.location.href = url;
  }
}
</script>
</head>

<?php
require("rb-mysql.php");

require 'db.php';
R::setup(dbCredentials());

if (!empty($_GET["floor"])) {
  $device = R::getAll(
    'SELECT * FROM device WHERE _floor = :floor',
    [':floor' => $_GET['floor']]
  );
}
?>

<body>
<?php require("menu.php"); ?>
  </br>
  <table class="table table-striped table-fit" > 
    <tr>
    <th scope="col">Id</th>
    <th scope="col">Nazwa</th>
    <th width="100px" scope="col">Edytuj</th>
    <th width="100px" scope="col">Usuń</th>
    </tr>
    <?php foreach ($device as $d) : ?>
      <tr>
      <th scope="row"><?= $d['id'] ?></th>
        <td><?= $d['_name'] ?></td>
        <td><a href="edit_device.php?floor=<?= $d['_floor'] ?>&name=<?= $d['_name'] ?>">Edytuj</a></td>
        <td><a href="#" onclick="confirmDelete('delete_device.php?floor=<?= $d['_floor'] ?>&name=<?= $d['_name'] ?>')">Usuń</a></td>
      </tr>
    <?php endforeach; ?>
  </table>
  </br>

</body>