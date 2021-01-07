<style>
body {
  margin:0px;
  padding:0px;
  font-family: Arial, Helvetica, sans-serif;
}

.navbar2 {
  overflow: hidden;
  background-color: #333;
}

.navbar2 a {
  float: left;
  font-size: 16px;
  color: white;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none !important;
}

.dropdown2 {
  float: left;
  overflow: hidden;
}

.dropdown2 .dropbtn2 {
  font-size: 16px;  
  border: none;
  outline: none;
  color: white;
  padding: 14px 16px;
  background-color: inherit;
  font-family: inherit;
  margin: 0;
}

.navbar2 a:hover, .dropdown2:hover .dropbtn2 {
  background-color: black;
}

.navbar2 a:hover{
    color:white;
}

.dropdown-content2 {
  display: none;
  position: absolute;
  background-color: #f9f9f9;
  min-width: 160px;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
}

.dropdown-content2 a {
  float: none;
  color: black;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
  text-align: left;
}

.dropdown-content2 a:hover {
  background-color: #ddd;
  color:black;
}

.dropdown2:hover .dropdown-content2 {
  display: block;
}
</style>

<div class="navbar2">
<a href="./index.php">IoT House</a>
<a href="./add_device.php">Dodaj urządzenie</a>
    <?php for($i=1;$i<=5;$i++){ ?>
  <div class="dropdown2">
    <button class="dropbtn2">Piętro <?=$i ?> 
      <i class="fa fa-caret-down"></i>
    </button>
    <div class="dropdown-content2">
      <a href="./show.php?floor=<?=$i?>">Lista urządzeń</a>
      <a href="./change_settings.php?floor=<?=$i?>">Nastawianie urządzeń</a>
      <a href="./reports.php?floor=<?=$i?>">Raporty</a>
    </div>
  </div> 
    <?php } ?>
  <div class="dropdown2">
    <button class="dropbtn2">Ogród na dachu
      <i class="fa fa-caret-down"></i>
    </button>
    <div class="dropdown-content2">
      <a href="./show.php?floor=6">Lista urządzeń</a>
      <a href="./change_settings.php?floor=6">Nastawianie urządzeń</a>
      <a href="./reports.php?floor=6">Raporty</a>
    </div>
  </div> 
</div>
