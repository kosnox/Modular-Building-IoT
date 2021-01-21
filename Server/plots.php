<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
<style>
  h4{
    display:inline;
  }
.chart-container {
  position: relative;
  height: 100%;
  width: 100%;
}

.isResizable {
  background-color: #ffffff;
  margin: 0px auto;
  padding: 5px;
  border: 1px solid #d8d8d8;
  overflow: hidden;
  /* Not usable in IE */
  /* resize: both; */
  
  width: 800px;
  height: 400px;
  min-width: 280px;
  min-height: 280px;
  max-width: 1200px;
  max-height: 600px;
}
</style>
</head>
<?php if(empty($_GET["floor"]) || empty($_GET["name"]) || !isset($_GET["exclude"])): ?>
  <div class="alert alert-danger" role="alert">
  Nie podano wymaganych danych!
</div>
<?php die(); endif; ?>

<body>
<?php require("menu.php"); ?>
<h2>Raport dla urządzenia <?=$_GET["name"]?></h2>


<?php if(!empty($_GET["agg"])): ?>
  <div class="d-flex justify-content-center container ">
  <div class="row ">
    <h4 style="padding-right:5px;">Rodzaj wykresu:</h4>
    <select class="form-control"style="width:auto;" id="type" onchange="changeType()">
        <option value="line">Liniowy</option>
        <option value="bar">Słupkowy</option>
    </select>
  </div>
</div>
</br>
  <div class="isResizable">
    <div class="chart-container">
      <canvas id="chart"></canvas>
    </div>
</div>
<?php endif; ?>
<div class="d-flex justify-content-center container ">
<div class="row" style="margin-bottom:10px">
    <h4 style="padding-right:5px;">Dane z okresu:</h4>
<select class="form-control" style="width:auto;" id="interval" onchange="changeInterval()">
      <option value="hournow">Aktualna godzina</option>
      <option value="hourbefore">Poprzednia godzina</option>
      <option value="today">Dzisiaj</option>
      <option value="yesterday">Wczoraj</option>
      <option value="7days" selected>7 dni</option>
      <option value="30days">30 dni</option>
      <option value="year">Rok</option>
  </select>
  </div>
</div>
</br>
<?php if($_GET["agg"] == "count01"): ?>
<div class="row text-center">
<div class="col-sm">
<input type="checkbox" id="count01" onclick="changeInterval()" checked /> Pokaż tylko włączenia
</div>
</div>
</br>
<?php endif; ?>
<div class="Container">
  <div class="row text-center">
    <?php if (strpos($_GET["exclude"], 'count') === false): ?>
      <div class="col-sm"><h5>Ilość: </h5> <span id="count">0</span> </div>
    <?php endif; ?> 
    <?php if (strpos($_GET["exclude"], 'avg') === false): ?>
      <div class="col-sm"><h5>Średnia: </h5> <span id="avg">0</span></div>
    <?php endif; ?>
    <?php if (strpos($_GET["exclude"], 'min') === false): ?>
      <div class="col-sm"><h5>Min: </h5> <span id="min">0</span></div>
    <?php endif; ?>
    <?php if (strpos($_GET["exclude"], 'max') === false): ?>
      <div class="col-sm"><h5>Max: </h5> <span id="max">0</span></div>
    <?php endif; ?>

  </div>
</div>


</body>
<script src="js/chartjs/Chart.js"></script>
<script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
  
<script>
var myChart = null;

function changeType(){
  myChart.destroy();
  var interval = document.getElementById("interval").value;
  getChart(interval);
}

function changeInterval(){
  var interval = document.getElementById("interval").value;
  if(myChart != null){
    myChart.destroy();
    getChart(interval);
  }
  getInfo(interval)
}


function createChart(type,title,measurement,xArray,yArray,xAxis,yAxis){
  var ctx = document.getElementById('chart').getContext('2d');
  var dataset;
  if(type == "line"){
    dataset = [{
            label: measurement,
            data: yArray, 
            backgroundColor: 'transparent',
            pointBorderColor: 'orange',
            pointBackgroundColor: 'rgba(255,150,0,0.5)',
            pointHoverRadius: 10,
            pointHitRadius: 30,
            pointBorderWidth: 2,
            borderColor: 'red'
        }]
  }else{
    if(type == "bar"){
      var randomColorGenerator = function () { 
          return '#' + (Math.random().toString(16) + '0000000').slice(2, 8); 
      };

      var colorArray = [];
      for(var i = 0; i < xArray.length; i++)
        colorArray.push(randomColorGenerator());

      dataset = [{
            label: title,
            data: yArray, 
            backgroundColor: colorArray,
        }]
    }
  }

  myChart = new Chart(ctx, {
    type: type,
    data: {
        labels: xArray,
        datasets: dataset
    },
    options: {
        responsive: true,
        width:100,
        height:100,
        maintainAspectRatio: false,
        title: {
            display: true,
            text: title
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                  },
                  scaleLabel: {
                    display: true,
                    labelString: measurement
                    }
            }],
            xAxes: [{
                  scaleLabel: {
                    display: true,
                    labelString: "Czas"
                    }
            }]
            
        }
    }
});
}

function chartHour(chartData){
  var xChart = [];
  var yChart = [];
for(var i = 0; i < 60; i+=10){
  xChart.push(i);
}

if(chartData != null){
  for(var i = 0; i < 6; i++){
  if(chartData.length > 0){
    if(chartData[0]['minute'] == i*10){
      yChart.push(parseFloat(chartData[0]['data']));
      chartData.splice(0, 1);
    }
    else{
    yChart.push(0);
  }
  }
  else{
    yChart.push(0);
  }
}
}else{
  yChart.fill(0,0,5);
}
  return {"x" : xChart, "y":yChart};
}

function chartDay(chartData,today){
  var xChart = [];
  var yChart = [];
  var h = 24;

  if(today){
    var d = new Date();
    h = d.getHours();
  }
    
  for(var i = 0; i <= h; i++){
    xChart.push(i);
  }
if(chartData != null){
  for(var i = 0; i <= h; i++){
  if(chartData.length > 0){
    if(chartData[0]['hour'] == i){
      yChart.push(parseFloat(chartData[0]['data']));
      chartData.splice(0, 1);
    }
    else{
    yChart.push(0);
  }
  }
  else{
    yChart.push(0);
  }
}
}else{
  yChart.fill(0,0,5);
}

  return {"x" : xChart, "y":yChart};
}


function chartDate(chartData,days){
  var xChart = [];
  var yChart = [];

  var d = new Date();
      d.setDate(d.getDate()-days);
      for(var i = 0; i <= days ; i++){
        var datestring = ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2);
        
        xChart.push(datestring);
        d.setDate(d.getDate()+1);
      }


  if(chartData != null){
    for(var i = 0; i <= xChart.length; i++){
      if(chartData.length > 0){
        if(chartData[0]['date'].substring(5,chartData[0]['date'].length) == xChart[i]){
          yChart.push(parseFloat(chartData[0]['data']));
          chartData.splice(0, 1);
        }
        else{
        yChart.push(0);
      }
      }
      else{
        yChart.push(0);
      }
    }
  }
  else{
    yChart.fill(0,0,days-1);
  }
  return {"x" : xChart, "y":yChart};
}

function chartYear(chartData,current){
  var xChart = ["styczeń","luty","marzec","kwieceń","maj","czerwiec","lipiec","sierpień","wrzesień","październik","listopad","grudzień"];
  var yChart = [];

  if(current){
    var d = new Date();
    m = d.getMonth();
  }
if(chartData != null){
  for(var i = 0; i <= xChart.length; i++){
  if(chartData.length > 0){
    if(chartData[0]['month'] == i+1){
      yChart.push(parseFloat(chartData[0]['data']));
      chartData.splice(0, 1);
    }
    else{
    yChart.push(0);
  }
  }
  else{
    yChart.push(0);
  }
}
}else{
  yChart.fill(0,0,11);
}

  return {"x" : xChart, "y":yChart};
}

function getChart(interval){
var floor = getDataFromUrl("floor");
var name = getDataFromUrl("name");
var agg = getDataFromUrl("agg");
var url = "http://phpsandbox.cba.pl/api/iot/chartdata.php";
var arguments = "?floor=" + floor + "&name=" + name +"&agg=" + agg + "&interval=" + interval;
if(agg == "count01"){
  var checked = document.getElementById("count01").checked ? 1 : 0;
  arguments += "&where=" + checked;
}
var jqxhr = $.get( url + arguments, function() {
})
  .done(function(response) {
    var chartArray = null;
    var chartInfo = response["info"];
    var chartData = response["data"];
    switch(interval){
      case "hournow":
      case "hourbefore":
        chartArray = chartHour(chartData); 
      break;
      
      case "today":
        chartArray = chartDay(chartData,true);
      break;
      case "yesterday":
        chartArray = chartDay(chartData,false);
      break;
      case "7days":
        chartArray = chartDate(chartData,6);
      break;
      case "30days":
        chartArray = chartDate(chartData,29);
      break;
      case "year":
        chartArray = chartYear(chartData,true);
      break;
    }
    var type = document.getElementById("type").value;
    createChart(type,chartInfo['title'],chartInfo.measurement,chartArray.x,chartArray.y,"Czas",chartInfo['measurement']);
  })
  .fail(function(response) {
    alert("Błąd podczas pobierania wykresu: " + response["error"]);
    var type = document.getElementById("type").value;
    createChart(type,"","avg",null,null,"Czas","");
  });
}

function getInfo(interval){
var floor = getDataFromUrl("floor");
var name = getDataFromUrl("name");
var interval = document.getElementById("interval").value;
var url = "http://phpsandbox.cba.pl/api/iot/device_data.php";
var arguments = "?floor=" + floor + "&name=" + name + "&interval=" + interval;
var jqxhr = $.get( url + arguments, function() {
})
  .done(function(response) {
    document.getElementById("count").textContent = response[0]["count"] != null ? response[0]["count"] : 0;
    document.getElementById("avg").textContent = response[0]["avg"] != null ? response[0]["avg"] : 0;
    document.getElementById("min").textContent = response[0]["min"] != null ? response[0]["min"] : 0 ;
    document.getElementById("max").textContent = response[0]["max"] != null ? response[0]["max"] : 0 ;
  })
  .fail(function() {
    document.getElementById("count").textContent = 0;
    document.getElementById("avg").textContent = 0;
    document.getElementById("min").textContent = 0;
    document.getElementById("max").textContent = 0;
  });
}

function getDataFromUrl(param){
  let params = new URLSearchParams(location.search);

  return params.get(param);
}
if(getDataFromUrl("agg") != "")
    getChart("7days");

getInfo("7days");

</script>