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
<h2>Raport porównawczy dla urządzenia <?=$_GET["name"]?></h2>


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
<h4 style="padding-right:5px;">Dane z okresu:</h4>
</div>
<div class="d-flex justify-content-center container ">
<div class="row" style="margin-bottom:10px">
  <input type="date" id="startdate1" /> - <input type="date" id="enddate1" />
  </div>
</div>
<div class="d-flex justify-content-center container ">
<div class="row" style="margin-bottom:10px">
  <input type="date" id="startdate2" /> - <input type="date" id="enddate2" />
  </div>
</div>

<div class="d-flex justify-content-center container ">
<div class="row" style="margin-bottom:10px">
  <input type="button" onclick="clickUpdate()" value="Zmień zakres"> 
  </div>
</div>

</br>
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
  <div class="row text-center">
    <?php if (strpos($_GET["exclude"], 'count') === false): ?>
      <div class="col-sm"> <span id="count2">0</span> </div>
    <?php endif; ?> 
    <?php if (strpos($_GET["exclude"], 'avg') === false): ?>
      <div class="col-sm"> <span id="avg2">0</span></div>
    <?php endif; ?>
    <?php if (strpos($_GET["exclude"], 'min') === false): ?>
      <div class="col-sm"> <span id="min2">0</span></div>
    <?php endif; ?>
    <?php if (strpos($_GET["exclude"], 'max') === false): ?>
      <div class="col-sm"><span id="max2">0</span></div>
    <?php endif; ?>

  </div>
  <hr/>
  <div class="row text-center">
    <?php if (strpos($_GET["exclude"], 'count') === false): ?>
      <div class="col-sm"><span id="countCompare">0</span> </div>
    <?php endif; ?> 
    <?php if (strpos($_GET["exclude"], 'avg') === false): ?>
      <div class="col-sm"><span id="avgCompare">0</span></div>
    <?php endif; ?>
    <?php if (strpos($_GET["exclude"], 'min') === false): ?>
      <div class="col-sm"><span id="minCompare">0</span></div>
    <?php endif; ?>
    <?php if (strpos($_GET["exclude"], 'max') === false): ?>
      <div class="col-sm"><span id="maxCompare">0</span></div>
    <?php endif; ?>

  </div>
</div>
</br>

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
  getChart();
}

function createChart(type,title,measurement,chartArray,chartArray2,xAxis,yAxis){
  xArray = chartArray.x;
  yArray = chartArray.y;
 
  xArray2 = chartArray2.x;
  yArray2 = chartArray2.y;

  var ctx = document.getElementById('chart').getContext('2d');
  var dataset,dataset2;
  if(type == "line"){
    dataset = [{
            label: xArray[0] + " / " + xArray[xArray.length-1],
            data: yArray, 
            backgroundColor: 'transparent',
            pointBorderColor: 'orange',
            pointBackgroundColor: 'rgba(255,150,0,0.5)',
            pointHoverRadius: 10,
            pointHitRadius: 30,
            pointBorderWidth: 2,
            borderColor: 'red'
        },
        {
            label: xArray2[0] + " / " + xArray2[xArray2.length-1],
            data: yArray2, 
            backgroundColor: 'transparent',
            pointBorderColor: 'orange',
            pointBackgroundColor: 'rgba(255,150,0,0.5)',
            pointHoverRadius: 10,
            pointHitRadius: 30,
            pointBorderWidth: 2,
            borderColor: 'orange'
        }];
  }else{
    if(type == "bar"){
      var randomColorGenerator = function () { 
          return '#' + (Math.random().toString(16) + '0000000').slice(2, 8); 
      };
      var randColor = randomColorGenerator();
      var colorArray = [];
      
      for(var i = 0; i < xArray.length; i++)
        colorArray.push(randColor);
        randColor = randomColorGenerator();
        var colorArray2 = [];

      for(var i = 0; i < xArray2.length; i++)
        colorArray2.push(randColor);

      dataset = [{
            label: xArray[0] + " / " + xArray[xArray.length-1],
            data: yArray, 
            backgroundColor: colorArray,
        },
        {
            label: xArray2[0] + " / " + xArray2[xArray2.length-1],
            data: yArray2, 
            backgroundColor: colorArray2,
        }]
    }
  }

var arrayData = [dataset,dataset2];

var labelsArray = [];
if(xArray.length >= xArray2.length){
  for(var i = 0; i < xArray.length;i++){
    var x2 =  xArray2[i] != null ? " / " + xArray2[i] : "";
  labelsArray.push(xArray[i] +  x2);
}
}else{
  for(var i = 0; i < xArray2.length;i++){
    var x1 =  xArray[i] != null ?  xArray[i] + " / " : "";
  labelsArray.push(x1 + xArray2[i] );
}
}


console.log(arrayData);
  myChart = new Chart(ctx, {
    type: type,
    data: {
        labels: labelsArray,
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

function chartDate(chartData,startdate,days){
  var xChart = [];
  var yChart = [];
  var d = new Date(startdate);
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
      }
    }
  }
  else{
    yChart.fill(0,0,days-1);
  }
  return {"x" : xChart, "y":yChart};
}

function getChart(){
var floor = getDataFromUrl("floor");
var name = getDataFromUrl("name");
var agg = getDataFromUrl("agg");
var startdate = document.getElementById("startdate1").value;
var enddate = document.getElementById("enddate1").value;
var startdate2 = document.getElementById("startdate2").value;
var enddate2 = document.getElementById("enddate2").value;
var url = "http://phpsandbox.cba.pl/api/iot/chartdata_date.php";
var arguments = "?floor=" + floor + "&name=" + name +"&agg=" + agg + "&startdate=" + startdate + "&enddate=" + enddate + "&startdate2=" + startdate2 + "&enddate2=" + enddate2;
var jqxhr = $.get( url + arguments, function() {
})
  .done(function(response) {
    var chartArray = null;
    var chartInfo = response["info"];
    var chartData = response["data"];
    var chartData2 = response["data2"];

    chartArray = chartDate(chartData,startdate,days_between(Date.parse(startdate),Date.parse(enddate)));
    chartArray2 = chartDate(chartData2,startdate2,days_between(Date.parse(startdate2),Date.parse(enddate2)));

    var type = document.getElementById("type").value;
    createChart(type,chartInfo['title'],chartInfo.measurement,chartArray,chartArray2,"Czas",chartInfo['measurement']);
  })
  .fail(function(response) {

    alert("Błąd podczas pobierania wykresu: " + response["error"]);
    var type = document.getElementById("type").value;
    createChart(type,"","avg",null,null,"Czas","");
  });
}

function getInfo(){
var floor = getDataFromUrl("floor");
var name = getDataFromUrl("name");
var startdate = document.getElementById("startdate1").value;
var enddate = document.getElementById("enddate1").value;
var startdate2 = document.getElementById("startdate2").value;
var enddate2 = document.getElementById("enddate2").value;
var url = "http://phpsandbox.cba.pl/api/iot/device_data_date.php";
var arguments = "?floor=" + floor + "&name=" + name + "&enddate="+enddate+ "&startdate="+startdate+"&enddate2="+enddate2+"&startdate2=" + startdate2;
var jqxhr = $.get( url + arguments, function() {
})
  .done(function(response) {
    document.getElementById("count").textContent =response[0][0]["count"];
    document.getElementById("avg").textContent = response[0][0]["avg"];
    document.getElementById("min").textContent = response[0][0]["min"];
    document.getElementById("max").textContent = response[0][0]["max"];

    document.getElementById("count2").textContent =response[1][0]["count"];
    document.getElementById("avg2").textContent = response[1][0]["avg"];
    document.getElementById("min2").textContent = response[1][0]["min"];
    document.getElementById("max2").textContent = response[1][0]["max"];

    document.getElementById("countCompare").textContent = response[0][0]["count"] - response[1][0]["count"];
    document.getElementById("avgCompare").textContent = (response[0][0]["avg"] - response[1][0]["avg"]).toFixed(2);
    document.getElementById("minCompare").textContent = response[0][0]["min"] - response[1][0]["min"];
    document.getElementById("maxCompare").textContent = response[0][0]["max"] - response[1][0]["max"];

  })
  .fail(function(response) {
    document.getElementById("count").textContent = 0;
    document.getElementById("avg").textContent = 0;
    document.getElementById("min").textContent = 0;
    document.getElementById("max").textContent = 0;

    document.getElementById("count2").textContent = 0;
    document.getElementById("avg2").textContent = 0;
    document.getElementById("min2").textContent = 0;
    document.getElementById("max2").textContent = 0;

    document.getElementById("countCompare").textContent = 0;
    document.getElementById("avgCompare").textContent = 0;
    document.getElementById("minCompare").textContent = 0;
    document.getElementById("maxCompare").textContent = 0;
  });
}

function getDataFromUrl(param){
  let params = new URLSearchParams(location.search);

  return params.get(param);
}

function days_between(date1, date2) {
// The number of milliseconds in one day
const ONE_DAY = 1000 * 60 * 60 * 24;
// Calculate the difference in milliseconds
const differenceMs = Math.abs(date1 - date2);
// Convert back to days and return
return Math.round(differenceMs / ONE_DAY);
}

function clickUpdate(){
  myChart.destroy();
  getInfo();
  getChart();
}

var d = new Date();

d.setDate(d.getDate());
document.getElementById("enddate1").value = d.toISOString().split('T')[0];
d.setDate(d.getDate()-3);
document.getElementById("startdate1").value = d.toISOString().split('T')[0];
d.setDate(d.getDate()-1);
document.getElementById("enddate2").value = d.toISOString().split('T')[0];
d.setDate(d.getDate()-3);
document.getElementById("startdate2").value = d.toISOString().split('T')[0];


if(getDataFromUrl("agg") != "")
    getChart();

getInfo();





</script>