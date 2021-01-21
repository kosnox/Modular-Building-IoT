#include <OneWire.h>
#include <ArduinoJson.h>
#include <DallasTemperature.h>
#include <ESP8266WiFi.h>
#include <esp8266httpclient.h>
#include <WiFiUdp.h>
#include <MySQL_Connection.h>
#include <MySQL_Cursor.h>

#include "DHT.h"
#define ONE_WIRE_BUS 0
#define DHTPIN 2
#define DHTTYPE DHT11 

#ifndef STASSID
#define STASSID "test anteny 5G"
#define STAPSK  "Bukowska8"
#endif

char hostname[] = "mysql.cba.pl";
IPAddress server_ip;  // IP of the MySQL *server* here
char user[] = "dbmaster";              // MySQL user login username
char password[] = "Admin123";        // MySQL user login password
char db[]="cargoalgps";


char findquery[] ="SELECT id FROM device WHERE _auto = 1";
char query[] = "SELECT value FROM devicesettings where device_id = 100";
char query1[] = "SELECT value FROM devicesettings where device_id = 101";
const char* dane = "http://phpsandbox.cba.pl/api/iot/save.php?floor=6";


char ssid[] = "test anteny 5G";         // your SSID
char pass[] = "Bukowska8";     // your SSID Password

WiFiClient client;              
MySQL_Connection conn((Client *)&client);
MySQL_Cursor cur = MySQL_Cursor(&conn);

unsigned int localPort = 8888;  
char packetBuffer[UDP_TX_PACKET_MAX_SIZE + 1];
char  ReplyBuffer[] = "acknowledged\r\n";     
const int HumidityPin = A0;
const int SunlightPin = 5;
const int WATERPIN=14;
const int HEATERPIN =4;
float Humidity_G,Sunlight,Humidity,Temperature_G,Temperature;             
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);
//WiFiUDP Udp;
DHT dht(DHTPIN, DHTTYPE);
StaticJsonBuffer<200> doc;
StaticJsonBuffer<200> doc1;
StaticJsonBuffer<200> doc2;
float getIntX(String serverReply, int x) {

  float wantedVariable;
  JsonArray& op = doc.parseArray(serverReply);
  String word0 = op[0];
  String word1 = op[1];
  JsonObject& obj1 = doc1.parseObject(word0);
  JsonObject& obj2 = doc2.parseObject(word1);
  doc.clear();
  doc1.clear();
  doc2.clear();

  if (x == 1)
  {
    wantedVariable = obj1["value"];
    //Serial.println(wantedVariable);
    return wantedVariable;
  }
  else if (x == 3)
  {
    wantedVariable = obj1["value"];
    //Serial.println(wantedVariable);
    return wantedVariable;
  }
  else if (x == 2)
  {
    wantedVariable = obj2["value"];
    //Serial.println(wantedVariable);
    return wantedVariable;
  }
}
String httpGETDATA(const char* serverLink) {
  HTTPClient http;

  //  Vader the time has come
  http.begin(String(serverLink));

  // Do it,... do it now
  int httpReply = http.GET();

  String package = "--";


  if (httpReply > 0)  {
    Serial.print("JSON DATA READ \n");
    package = http.getString();
  }
  else {
    Serial.print("ERROR READING JSON \n ");
    Serial.print(httpReply);
  }
  // Vader release him at once
  http.end();

  return package;
}
void setup() {
    Serial.begin(9600);
  WiFi.mode(WIFI_STA);
  WiFi.begin(STASSID, STAPSK);
    sensors.begin();
    dht.begin();

   while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  WiFi.hostByName(hostname, server_ip);
  // print out info about the connection:
  Serial.println("\nConnected to network");
  Serial.print("My IP address is: ");
  Serial.println(WiFi.localIP());

  Serial.print("Connecting to SQL...  ");
  if (conn.connect(server_ip, 3306, user, password,db))
    Serial.println("OK.");
  else
    Serial.println("FAILED.");
}
void loop() {
  Humidity_G = map(analogRead(HumidityPin), 0, 1023, 100, 0);
  Sunlight = digitalRead(SunlightPin);
  Humidity = dht.readHumidity();
  Temperature = dht.readTemperature();
  sensors.requestTemperatures();
  Temperature_G=sensors.getTempCByIndex(0);
  
    
row_values *row = NULL;
  int pompa = 0;
  int grzalka = 0;
  int check =0;
  bool a_grzalka=false;
  bool a_pompa=false;
 delay(500);
  MySQL_Cursor *cur_mem = new MySQL_Cursor(&conn);
    cur_mem->execute(findquery);
 column_names *columns = cur_mem->get_columns();
 // cur_mem = new MySQL_Cursor(&conn);
  do {
    row = cur_mem->get_next_row();
    if (row != NULL) {
      check = atol(row->values[0]);
      if(check==100)a_pompa=true;
      else if(check==101)a_grzalka=true;
    }
  } while (row != NULL);
    delete cur_mem;
     delay(500);
if(a_pompa){
  delay(1000);
  // Initiate the query class instance
  cur_mem = new MySQL_Cursor(&conn);
  cur_mem->execute(query);
  columns = cur_mem->get_columns();
  do {
    row = cur_mem->get_next_row();
    if (row != NULL) {
      pompa = atol(row->values[0]);
    }
  } while (row != NULL);
  
  
    delete cur_mem;
    
  Serial.print(" odczytana auto pompa= ");
  Serial.println(pompa);
}
else{
if (WiFi.status() == WL_CONNECTED) {

      String serverReply = httpGETDATA("http://phpsandbox.cba.pl/api/iot/settings.php?floor=6&dev=pompa_wody");
      grzalka = getIntX(serverReply, 1);
}
}
 delay(500);
if(a_grzalka){


  cur_mem = new MySQL_Cursor(&conn);
  cur_mem->execute(query1);
  columns = cur_mem->get_columns();
  do {
    row = cur_mem->get_next_row();
    if (row != NULL) {
      grzalka = atol(row->values[0]);
    }
  } while (row != NULL);
    delete cur_mem;
    
  Serial.print("  odczytana auto grzalka = ");
  Serial.println(grzalka);
}
else
{
if (WiFi.status() == WL_CONNECTED) {

      String serverReply = httpGETDATA("http://phpsandbox.cba.pl/api/iot/settings.php?floor=6&dev=ogrzewanie");
       pompa= getIntX(serverReply, 1);
}
}

  
if ((WiFi.status() == WL_CONNECTED)) {
  String server = String(dane)
 +"&temperatura_gleba="+String(Temperature_G)
 +"&temperatura_powietrza="+String(Temperature)
 +"&wilgotnosc_powietrza="+String(Humidity)
 +"&wilgotnosc_gleby="+String(Humidity_G)
 +"&naslonecznienie="+String(Sunlight);
 if(a_pompa)server+= "&pompa_wody="+String(pompa);
 if(a_grzalka)server+= "&ogrzewanie="+String(grzalka);

  String server2 =String(server);
  HTTPClient https;
  https.begin(server2); 
  Serial.println(server2);
   int httpCode = https.GET();                                                                  //Send the request
   if (httpCode > 0) { //Check the returning code
    Serial.println("1");                     //Print the response payload
   }
}
analogWrite(HEATERPIN,grzalka);
analogWrite(WATERPIN,pompa);

  delay(30000);
}

