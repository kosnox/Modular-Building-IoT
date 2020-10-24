#include "mbed.h"
#include "AES.h"
#include <string>

class ESP01 {
private:
    Serial *_wifi;
    const char *_key;
    const char *_iv;
    AES _aes;
public:
    ESP01(PinName tx, PinName rx, int baud = 9600);
    ~ESP01();
    bool echoFind(string keyword, uint32_t timeout_ms);
    bool sendCommand(string cmd, string ack, uint32_t timeout_ms);
    bool connect(string name, string password);
    bool sendString(string msg, uint32_t timeout_ms = 5000);
    bool sendBytes(const char *msg, uint16_t size, uint32_t timeout_ms = 5000);
    bool readToString(string &msg);
    void flush();
    void setupAES(const char *key, const char *iv);
    bool sendCodedBytes(const char *msg, uint16_t size, uint32_t timeout_ms = 5000);
    bool connectClient(string address, string port);
    bool sendGETRequest(string request, string host, string &respond, uint32_t timeout_ms = 5000);
};
