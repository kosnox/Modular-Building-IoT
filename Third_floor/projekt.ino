#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <Servo.h>
#include <DHT.h>;
#define led_pin 16 //D0
#define dht_pin 5 //D1
#define servo_pin 14 //D5
#define fan_pwm_pin 12 //D6
#define fan_pin 13 //D7
DHT dht(dht_pin, DHT22);
Servo servo;
const char* ssid = "Huawei Wi-Fi 2,4G";
const char* password = "";
String names[] = {"wiatrak_nastaw","wiatrak_PWM_nastaw","temperatura_nastaw","jasnosc_nastaw","rolety_nastaw","led_nastaw"};
float configs[] = {-2, -2, -2, -2, -2, -2};
bool bools[] = {false, false, false, false, false, false};
float temp;
float hum;
float lux;
int led_pwm = 3;
unsigned long lastGetConfigsTime = 0;
unsigned long lastUploadDataTime = 0;
unsigned long timerGetConfigsDelay = 5000;
unsigned long timerUploadDataDelay = 5000;
void setup() {
  Serial.begin(115200); 
  pinMode(led_pin, OUTPUT);
  pinMode(fan_pwm_pin, OUTPUT);
  pinMode(fan_pin, OUTPUT);
  pinMode(servo_pin, OUTPUT);
  pinMode(dht_pin, INPUT);
  dht.begin();
  servo.attach(servo_pin);
  servo.write(0);
  connectWiFi();
}
void loop() {
  getConfigs();
  uploadData();
  steeringFans();
  steeringServo();
}
void getConfigs(){
  if ((millis() - lastGetConfigsTime) > timerGetConfigsDelay) {
    if(WiFi.status()== WL_CONNECTED){
      for(int i=0; i<6; ++i){
        String url = "http://phpsandbox.cba.pl/api/iot/settings.php?floor=3&dev=" + names[i];
        String request = httpGETRequest(url.c_str());
        if(request == "--"){
          Serial.print("Missed data for ");
          Serial.println(names[i]);
          continue;
        }
        StaticJsonBuffer<200> doc;
        JsonArray& op = doc.parseArray(request);
        String word0 = op[0];
        JsonObject& obj = doc.parseObject(word0);
        float k = static_cast<float>(obj["value"]);
        if(abs(k - configs[i]) > 0.1){
          configs[i] = k;
          bools[i] = true;
        }
        //Serial.print(names[i]);
        //Serial.print("= ");
        //Serial.println(configs[i]);
      }
    }
    lastGetConfigsTime = millis();
  }
}
float getAverageLux(){
  float sum = 0;
  for(int i = 0; i < 20; ++i){
    sum += analogRead(A0)*0.9765625;
    delay(1);
  }
  return sum / 20.0;
}
void steeringFans(){
  //fan
  if(configs[2]==0){
    digitalWrite(fan_pin, configs[0]);
  }
  else if(configs[2] + 2.0 > temp){
    digitalWrite(fan_pin, HIGH);
  }
  else {
    Serial.println("Nieprawidlowa wartosc zadanej temperatury");
    Serial.println(configs[2]);
  }
  
  //fan_pwm
  analogWrite(fan_pwm_pin, static_cast<int>(configs[1]));
}

void steeringServo(){
  if(configs[3]==0){
    servo.write(configs[4]);
    analogWrite(led_pin, configs[5]);
  }
  else {
    if(bools[3] == false) {
      return;
    }
    if(configs[3] > lux){
      servo.write(0);
      led_pwm = 1023;
      delay(300);
      do {
        analogWrite(led_pin, led_pwm);
        lux = getAverageLux();
        led_pwm -= 10;
      }
      while(configs[3] < lux && led_pwm > 0);
      bools[3] = false;
      return;
    }
    if(configs[3] < lux){
      servo.write(60);
      led_pwm = 3;
      delay(300);
      do{
        analogWrite(led_pin, led_pwm);
        lux = getAverageLux();
        led_pwm += 10;
      }
      while(configs[3] > lux && led_pwm < 1024);
      bools[3] = false;
    }
  }
}

void uploadData(){
  if ((millis() - lastUploadDataTime) > timerUploadDataDelay) {
    if(WiFi.status()== WL_CONNECTED){
      String serverPath = "http://phpsandbox.cba.pl/api/iot/save.php?floor=3";
      temp = dht.readTemperature();
      if(!isnan(temp))
        serverPath += "&temperatura=" + String(temp);
      hum = dht.readHumidity();
      if(!isnan(hum))
        serverPath += "&wilgotnosc=" + String(hum);
      float buf = getAverageLux();
      if(abs(buf - configs[3]) > 10)
        bools[3] = true;
      lux = buf;
      serverPath += "&jasnosc=" + String(lux);
      httpGETRequest(serverPath.c_str());
    }
    else {
      Serial.println("WiFi Disconnected");
    }
    lastUploadDataTime = millis();
  }
}

void connectWiFi(){
  WiFi.begin(ssid, password);
  Serial.println("Connecting");
  while(WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());
}

String httpGETRequest(const char* serverName) {
  HTTPClient http;
  http.begin(serverName);
  int httpResponseCode = http.GET();
  String payload = "--";
  if (httpResponseCode>0) {
    payload = http.getString();
  }
  else if(httpResponseCode == 400)
    payload = "--"; 
  else {
    Serial.print("Error code: ");
    Serial.println(httpResponseCode);
  }
  
  http.end();
  return payload;
}
