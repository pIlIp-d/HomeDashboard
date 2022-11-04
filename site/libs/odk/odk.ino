
/*
Temperatursensor fuer Holzbackofen, Grill und Fleischthermometer
Sensoren:
  2 x MAX6675

  V06.08.2021 encryption addded to GetRequest
*/
#include <WiFi.h>
#include <max6675.h> 
#include <HTTPClient.h>
#include <string.h>
#include <iostream>
//for OTA updates
#include <ESPmDNS.h>
#include <WiFiUdp.h>
#include <ArduinoOTA.h>

using namespace std;

// Netzwerk-Einstellungen
const IPAddress local_IP(192, 168, 115, 240);
const IPAddress gateway(192, 168, 115, 254);
const IPAddress subnet(255, 255, 255, 0);
//const IPAddress primaryDNS(192, 168, 115, 254);   //Fritz-Box
const IPAddress primaryDNS(8, 8, 8, 8);   //optional
const IPAddress secondaryDNS(8, 8, 4, 4); //optional

const char* ssid = "";
const char* password = "";
const char* device_name = "ESP32 -- odk -- 001";
// Homeserver
const String homeserverurl = "http://192.168.115.9/HomeDashboard/";
HTTPClient http;

int max6675SO = 19;     // Serial Output
int max6675CLK = 18;    // Serial Clock
int max6675CS_1 = 16;    // Chip Select Sensor 1
int max6675CS_2 = 17;   // Chip Select Sensor 2
int max6675CS_3 = 5;   // Chip Select Sensor 3
int max6675CS_4 = 26;   // Chip Select Sensor 4
int max6675CS_5 = 25;   // Chip Select Sensor 5

MAX6675 ktc1(max6675CLK, max6675CS_1, max6675SO); 
MAX6675 ktc2(max6675CLK, max6675CS_2, max6675SO); 
MAX6675 ktc3(max6675CLK, max6675CS_3, max6675SO); 
MAX6675 ktc4(max6675CLK, max6675CS_4, max6675SO); 
MAX6675 ktc5(max6675CLK, max6675CS_5, max6675SO); 

String readActTemperature(int Sensor) {
  // Temperatur einlesen
  float tf;
  int t;
  int t_corr;

  switch(Sensor) {
  case 1:
    t = ktc1.readCelsius();
    break;
  case 2:
    t = ktc2.readCelsius();
    break;
  case 3:
    t = ktc3.readCelsius();
    break;
  case 4:
    t = ktc4.readCelsius();
    break;
  case 5:
    t = ktc5.readCelsius();
    break;
  }
  
  if (isnan(t)) {    
    Serial.print("Fehler beim Auslesen der Temperatur an Sensor ");
    Serial.print(Sensor);
    Serial.println(" !");
    return "--";
  }
  else {
    // Temperatur-Korrektur
    t_corr = curve_correction(t);
    Serial.print("Temperatur an Sensor ");
    Serial.print(Sensor);
    Serial.print(": ");
    Serial.println(t_corr);
    return String(t_corr);
  }
}

int curve_correction(int temp_in) {
  // Korrektur der Kennlinie
  int temp_out = 0;
  // 0 - 100 Grad
  if (temp_in <= 100) {
    temp_out = temp_in;
  }
  // 100-300 Grad
  if (temp_in > 100) {
    if (temp_in <= 300) {   
      temp_out = temp_in + (temp_in - 100) / 2;
    }
  }
  if (temp_in > 300) {
    temp_out = temp_in;
  }
  return temp_out;
}
void setup(){
  // Serial port
  Serial.begin(115200);
  // WLAN-Verbindung aufbauen
  WiFi.config(local_IP, gateway, subnet, primaryDNS, secondaryDNS);  
  WiFi.begin(ssid, password); 
  Serial.print("Verbinde mit WLAN .");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("WLAN verbunden: ");
  Serial.println(WiFi.localIP());

  //arduino OTA update init (from examples/arduinoOTA/BasicOTA
  ArduinoOTA.setHostname(device_name);
  ArduinoOTA
    .onStart([]() {
      String type;
      if (ArduinoOTA.getCommand() == U_FLASH)
        type = "sketch";
      else // U_SPIFFS
        type = "filesystem";

      // NOTE: if updating SPIFFS this would be the place to unmount SPIFFS using SPIFFS.end()
      Serial.println("Start updating " + type);
    })
    .onEnd([]() {
      Serial.println("\nEnd");
    })
    .onProgress([](unsigned int progress, unsigned int total) {
      Serial.printf("Progress: %u%%\r", (progress / (total / 100)));
    })
    .onError([](ota_error_t error) {
      Serial.printf("Error[%u]: ", error);
      if (error == OTA_AUTH_ERROR) Serial.println("Auth Failed");
      else if (error == OTA_BEGIN_ERROR) Serial.println("Begin Failed");
      else if (error == OTA_CONNECT_ERROR) Serial.println("Connect Failed");
      else if (error == OTA_RECEIVE_ERROR) Serial.println("Receive Failed");
      else if (error == OTA_END_ERROR) Serial.println("End Failed");
    });

  ArduinoOTA.begin();

  Serial.println("Ready");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
}

// all base64 chars for Encoding
static const char* B64chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";

//Base 64 encoder 
const std::string b64encode(const void* data, const size_t &len)
{
  //fill string of '='/NUL in length for base64 (x*24bit)
  std::string result((len + 2) / 3 * 4, '='); 
  unsigned char *p = (unsigned  char*) data;
  char *str = &result[0];
  size_t j = 0, pad = len % 3;
  const size_t last = len - pad;
  //base64 encode shift 
  for (size_t i = 0; i < last; i += 3)
  {
    int n = int(p[i]) << 16 | int(p[i + 1]) << 8 | p[i + 2];
    str[j++] = B64chars[n >> 18];
    str[j++] = B64chars[n >> 12 & 0x3F];
    str[j++] = B64chars[n >> 6 & 0x3F];
    str[j++] = B64chars[n & 0x3F];
  }
  if (pad){  // Set padding (last NUL chars for BASE64 24bit structure)
    int n = --pad ? int(p[last]) << 8 | p[last + 1] : p[last];
    str[j++] = B64chars[pad ? n >> 10 & 0x3F : n >> 2];
    str[j++] = B64chars[pad ? n >> 4 & 0x03F : n << 4 & 0x3F];
    str[j++] = pad ? B64chars[n << 2 & 0x3F] : '=';
  }
  return result;
}
//input -> into c_str and length -> b64encoder
String b64encode(const std::string& str)
{
  return b64encode(str.c_str(), str.size()).c_str();
}
//base64 encoder End


//encryption with XOR gate
string XOR(string data, char key[],int keylength)
{
  string output = data;
  for (int i = 0; i < output.size(); i++) 
  {
    output[i] = data[i] ^ key[i % keylength];//(sizeof(key) / sizeof(char))];
  }
  return output;
}

void sendvalue2homeserver(String t_act_top, String t_act_bottom, String t_act_left, String t_act_right, String t_act_meat){
  // Ãbertragung der Temperaturwerte an den HomeServer
  int returnCode;

    //encryption with OTP / XOR gate -> for good security keystr must be at least as long as the data string (~200chars, no specials)
    // \/ \/change json encryption key \/ \/ 
    string keystr = "";//change to the same key as in json_handler.php
    
    int keylength = keystr.size();
    char key[keylength];
    for (int i=0; i < keylength-1; i++)
    {
      key[i] = keystr[i];
    }
  
  String url = homeserverurl; 
  url += "json_handler.php?json={";  
  String data = "{\"device_id\":\"001\",";
    data += "\"event\":\"set_act_temp\",";
    data += "\"temp_act_top\":\"" + t_act_top + "\",";
    data += "\"temp_act_bottom\":\"" + t_act_bottom + "\",";
    data += "\"temp_act_left\":\"" + t_act_left + "\",";
    data += "\"temp_act_right\":\"" + t_act_right + "\",";
    data += "\"temp_act_meat\":\"" + t_act_meat + "\"}";
    //verschlÃ¼sseln
    string enc_data = XOR(data.c_str(),key,keylength);
    //base64 encoding
    url += "\"data\":\"" + b64encode(enc_data) + "\"}"; 
  // Verbindung zu HomeServer aufbauen
  http.begin(url); 
  Serial.println(url);
  returnCode = http.GET();        
  if (returnCode = 200){
    String payload = http.getString();
    Serial.print("Übertragung: ");
    Serial.println(payload);
  }
  else{
    Serial.print("Übertragungsfehler: ");
    Serial.println(returnCode);
  }
  http.end();
}

void sendvalue2debugserver(String message){
  int returnCode;
  
  String url = homeserverurl; 
  url += "1/";  
  String data = "Message: ";
    data += message;
  url += data;
  // Verbindung zu HomeServer aufbauen
  http.begin(url); 
  Serial.println(url);
  returnCode = http.GET();        
  if (returnCode = 200){
    String payload = http.getString();
    Serial.print("Übertragung: ");
    Serial.println(payload);
  }
  else{
    Serial.print("Übertragungsfehler: ");
    Serial.println(returnCode);
  }
  http.end();
}

void loop(){
  ArduinoOTA.handle();
  String temp_s1 = readActTemperature(1).c_str();
  //delay(500);
  String temp_s2 = readActTemperature(2).c_str();
  //delay(500);
  String temp_s3 = readActTemperature(3).c_str();
  //delay(500);
  String temp_s4 = readActTemperature(4).c_str();
  //delay(500);
  String temp_s5 = readActTemperature(5).c_str();
  //delay(500);
  sendvalue2homeserver(temp_s1, temp_s2, temp_s3, temp_s4, temp_s5);
  sendvalue2debugserver("Test");
  ArduinoOTA.handle();
  delay(2000);
}