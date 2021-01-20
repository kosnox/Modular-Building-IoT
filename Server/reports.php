<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

</head>

<?php
require("rb-mysql.php");

require 'db.php';
R::setup(dbCredentials());

if (!empty($_GET["floor"])) {
  $device = R::getAll(
    'SELECT d.*,MAX(dd._date) AS lastDate,dr.agg_fun,dr.exclude FROM device d LEFT JOIN devicedata dd ON d.id = dd.device_id LEFT JOIN devicereport dr ON d.id = dr.device_id WHERE _floor = :floor GROUP BY d.id',
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
    <th scope="col">Ostatnia modyfikacja</th>
    <th width="100px" scope="col">Dane</th>
    <th width="100px" scope="col">Podsumowanie</th>
    <th width="100px" scope="col">Porównaj</th>
    </tr>
    <?php foreach ($device as $d) : ?>
      <tr>
      <th scope="row"><?= $d['id'] ?></th>
        <td><?= $d['_name'] ?></td>
        <td><?= $d['lastDate'] ?></td>
        <?php if(strlen($d['lastDate'])>0) : ?>
        <td><a href="device_data_table.php?floor=<?=$d['_floor']?>&name=<?= $d['_name'] ?>">Dane</a></td>
        <td><a href="plots.php?floor=<?=$d['_floor']?>&name=<?= $d['_name'] ?>&agg=<?= $d['agg_fun'] ?>&exclude=<?= $d['exclude'] ?>">Podsumowanie</a></td>
        <td><a href="plot_compare.php?floor=<?=$d['_floor']?>&name=<?= $d['_name'] ?>&agg=<?= $d['agg_fun'] ?>&exclude=<?= $d['exclude'] ?>">Porównaj</a></td>
        <?php else : ?>
          <td></td>
          <td></td>
          <td></td>
        <?php endif; ?>
        </tr>
    <?php endforeach; ?>
  </table>
  </br>

</body>