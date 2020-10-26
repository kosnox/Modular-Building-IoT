#include "mbed.h"
#include "ESP01.h"
#include "MFRC522.h"
#include <string>

using namespace std;

EventQueue queue(32 * EVENTS_EVENT_SIZE);
Thread t;
Mutex printingMutex;
Timeout timeoutDoors;
Timeout timeoutLight;

// Communication
ESP01 esp(D14, D15, 9600);

// Outputs
DigitalOut doors(D7);
DigitalOut light(D2);
PwmOut heater(D5);

// Sensors
MFRC522 RfChip(SPI_MOSI, SPI_MISO, SPI_SCK, SPI_CS, D8);
int hydrogenRaw, odorRaw, ammoniaRaw, methaneRaw, humidity;
float temperature;
InterruptIn movement(D4);

bool lightsOn = true;
int heaterPercent = 0;


void closeDoors()
{
    doors = 0;
}

void turnOffLight()
{
    if(movement.read() == 1){
        light = 1;
        timeoutLight.attach(&turnOffLight, 20.0);
    } else {
        light = 0;
    }
}

void movementDetected()
{
    if(lightsOn){
        light = 1;
        timeoutLight.attach(&turnOffLight, 20.0);
    }
}


// Periodical functions
bool readAir(void)
{
    string dataAir;
    esp.connectClient("192.168.1.37", "80");
    esp.readToString(dataAir);
    printf("%s\r\n", dataAir.c_str());

    if(dataAir.find("ERROR")!=string::npos) return false;

    size_t positionStart = dataAir.find("hydrogen=");
    if (positionStart == string::npos) return false;
    size_t positionEnd = dataAir.find("&", positionStart);
    if (positionEnd == string::npos) return false;
    hydrogenRaw = stoi(dataAir.substr(positionStart + 9 , positionEnd - positionStart - 9));
    printf("%d\r\n", hydrogenRaw);
    
    positionStart = dataAir.find("odor=");
    if (positionStart == string::npos) return false;
    positionEnd = dataAir.find("&", positionStart);
    if (positionEnd == string::npos) return false;
    odorRaw = stoi(dataAir.substr(positionStart + 5 , positionEnd - positionStart - 5));
    printf("%d\r\n", odorRaw);

    positionStart = dataAir.find("ammonia=");
    if (positionStart == string::npos) return false;
    positionEnd = dataAir.find("&", positionStart);
    if (positionEnd == string::npos) return false;
    ammoniaRaw = stoi(dataAir.substr(positionStart + 8 , positionEnd - positionStart - 8));
    printf("%d\r\n", ammoniaRaw);

    positionStart = dataAir.find("methane=");
    if (positionStart == string::npos) return false;
    positionEnd = dataAir.find("&", positionStart);
    if (positionEnd == string::npos) return false;
    methaneRaw = stoi(dataAir.substr(positionStart + 8 , positionEnd - positionStart - 8));
    printf("%d\r\n", methaneRaw);

    positionStart = dataAir.find("temperature=");
    if (positionStart == string::npos) return false;
    positionEnd = dataAir.find("&", positionStart);
    if (positionEnd == string::npos) return false;
    temperature = stof(dataAir.substr(positionStart + 12 , positionEnd - positionStart - 12));
    printf("%f\r\n", temperature);

    positionStart = dataAir.find("humidity=");
    if (positionStart == string::npos) return false;
    humidity = stoi(dataAir.substr(positionStart + 9));
    printf("%d\r\n", humidity);

    return true;
}

void postTemperatureHumidity()
{
    while(!readAir());

    string jakistam;
    esp.sendGETRequest("/api/iot/save.php?floor=1&termometr=" + to_string(temperature) + "&wilgotność=" + to_string(humidity), "cargoalgps.cba.pl", jakistam);
    printf("%s", jakistam.c_str());
}

void readCard()
{
    static const uint8_t card[] = {0x9E, 0xB2, 0xE4, 0xAB};
    // Look for new cards
    if (! RfChip.PICC_IsNewCardPresent()) {
        return;
    }

    // Select one of the cards
    if (! RfChip.PICC_ReadCardSerial()) {
        return;
    }
    char buf[3];
    string cardNr;

    for (uint8_t i = 0; i < RfChip.uid.size; i++) {
        sprintf(buf, "%02X", RfChip.uid.uidByte[i]);
        cardNr.append(buf);
    }
    
    string jakistam;
    esp.sendGETRequest("/api/iot/save.php?floor=1&logs=" + cardNr, "cargoalgps.cba.pl", jakistam);

    for (uint8_t i = 0; i < RfChip.uid.size; i++) {
        if (RfChip.uid.uidByte[i] != card[i]) {
            return;
        }
    }
    doors = 1;
    timeoutDoors.attach(&closeDoors, 5.0);
}

bool checkServer()
{
    string serverData;
    esp.sendGETRequest("/api/iot/settings.php?floor=1&dev=grzejnik&dev2=światła", "cargoalgps.cba.pl", serverData);
    printf("%s", serverData.c_str());

    size_t positionStart = serverData.find("grzejnik");
    if (positionStart == string::npos) return false;
    size_t positionEnd = serverData.find("}", positionStart);
    if (positionEnd == string::npos) return false;
    heaterPercent = stoi(serverData.substr(positionStart + 19 , positionEnd - positionStart - 20));
    printf("%d\r\n", heaterPercent);
    if(heaterPercent > 100) heaterPercent = 100;
    if(heaterPercent < 0) heaterPercent = 0;

    positionStart = serverData.find("\\u015bwiat\\u0142a");
    if (positionStart == string::npos) return false;
    positionEnd = serverData.find("}", positionStart);
    if (positionEnd == string::npos) return false;
    lightsOn = stoi(serverData.substr(positionStart + 28 , positionEnd - positionStart - 29));
    printf("%d\r\n", lightsOn);

    heater.pulsewidth(0.001f + 0.00001f*heaterPercent);

    return true;
}

int main()
{
    printf("Starting in context %p\r\n", ThisThread::get_id());
    RfChip.PCD_Init();

    // Fill up the event queue
    queue.call_every(500, readCard);
    queue.call_every(60*1000*5, postTemperatureHumidity);
    queue.call_every(60*1000, checkServer);

    // Start the event queue
    //t.start(callback(&queue, &EventQueue::dispatch_forever));

    // Connect ESP to wifi
    if (esp.connect("WIFINAME", "WIFIPASSWORD")) {
        printf("CONNECTED\r\n");
    } else {
        printf("ERROR ESP DIDN'T CONNECT\r\n");
    }

    // Start movement interrupts
    movement.mode(PullDown);
    movement.rise(&movementDetected);
    movement.fall(&movementDetected);
    heater.period_ms(20);

    checkServer();
    postTemperatureHumidity();

    while (1) {
        queue.dispatch_forever();
    }
}