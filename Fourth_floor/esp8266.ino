#include<PMV.h>
#include<ESP8266WiFi.h>
#include<ESP8266HTTPClient.h>
#include<ArduinoJson.h>
#include "DHT.h"
#define DHT11_PIN 2
#define DIODE 5

const char* ssid = "Kuba";
const char* password = "11111111";
const String serverNameDownloading =
  "http://phpsandbox.cba.pl/api/iot/settings.php?floor=4&dev=";
const String serverNameSending = 
  "http://phpsandbox.cba.pl/api/iot/save.php?floor=4&";
boolean prev = false;
unsigned long aktualny = 0;
unsigned long poprzedni1 = 0;
unsigned long poprzedni2 = 0;

StaticJsonDocument<200> doc;
DHT dht;
PMV pmv;


void setup () {
  Serial.begin(9600);
  WiFi.begin(ssid, password);
  dht.setup(DHT11_PIN);
  while (WiFi.status() != WL_CONNECTED){
    delay(1000);
    Serial.println("Łączenie...");
  }
  Serial.println("Połączono z Wi-Fi");
  Serial.print("Nadano adres IP: ");
  Serial.println(WiFi.localIP());
  pinMode(5, OUTPUT);
}
 
void loop() {
  aktualny = millis();
  if (WiFi.status() == WL_CONNECTED){
    
    if (aktualny - poprzedni2 >= 20000UL) {  //co 20sec
      poprzedni2 = aktualny;
      int temperatura = dht.getTemperature();
      int wilgotnosc = dht.getHumidity();
      double PMV = pmv.calculatePMV(temperatura, temperatura, 0.2, wilgotnosc, 1.2, 0.5, 0);
      int grzejnik = UstawGrzejnik(PMV);
      int klimatyzator = UstawKlimatyzator(PMV);
      int swiatlo = analogRead(A0);
      if (dht.getStatusString() == "OK") {
        WyslijDane("Temperatura", temperatura);
        WyslijDane("Wilgotnosc", wilgotnosc);
        WyslijDane("Swiatlo", swiatlo);
        WyslijDane("Grzejnik", grzejnik);
        WyslijDane("Klimatyzator", klimatyzator);
    }
    
    if (aktualny - poprzedni1 >= 5000UL) {  //co 5sec
      poprzedni1 = aktualny;
      
      String json = PobierzDane("TemperaturaNastaw");
      OdczytajJson(json);
      
      json = PobierzDane("SwiatloNastaw");
      OdczytajJson(json);
    }
  }
}
}

int UstawGrzejnik(double PMV){
  if(PMV < -2.5)
    return 100;
  else if(PMV > -0.5)
    return 0;
   else
    return (-50*PMV-25);
}

int UstawKlimatyzator(double PMV){
  if(PMV > 2.5)
    return 100;
  else if(PMV < 0.5)
    return 0;
   else
    return (50*PMV-25);
}

void WyslijDane(String czujnik, int wartosc)
{
  HTTPClient http;
  http.begin(serverNameSending+czujnik+"="+wartosc);
  int httpCode = http.GET();
  if (httpCode > 0) {
    Serial.println("Przesłano dane z czujnika " + czujnik 
                    + " o wartości " + wartosc + " na serwer");
  }
}

String PobierzDane(String nastaw)
{
  HTTPClient http;
  String payload;
  http.begin(serverNameDownloading+nastaw);
  int httpCode = http.GET();
  if (httpCode > 0) {
    payload = http.getString();
    Serial.println("");
    //Serial.println(payload);
  }
  http.end();
  payload.replace('[', ' ');
  payload.replace(']', ' ');
  return payload;
}

void OdczytajJson(String json)
{
  DeserializationError error = deserializeJson(doc, json);
  if (error) {
    Serial.print(F("Coś się popsuło"));
    Serial.println(error.c_str());
    return;
  }
  String device = doc["device"];
  int value = doc["value"];
  Serial.println("Ustawiona wartość dla urządzenia " + device + " to " + value);
  if(value == 0)
  {
    prev = false;
    digitalWrite(DIODE, LOW);
  }
  else
  {
    if(prev == false)
    {
      prev = true;
      digitalWrite(DIODE, HIGH);
    }
  }
}
