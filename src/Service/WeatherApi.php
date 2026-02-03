<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherApi
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }


    function GetCityInfos(string $cityName) : array {
        
        $cityName = str_replace(" ", "-", $cityName );
        

        $requete = $this->client->request(
            'GET',
            'https://geocoding-api.open-meteo.com/v1/search?name='.$cityName.'&count=1&language=en&format=json'
        );
        $content = $this->ExtractCityContentFromApi($requete->getContent());
        return $content;
    }

    function GetWeatherInfosFromCity(string $cityName) : array {


        $content = $this->GetCityInfos($cityName);
        if($content["is_empty"] != true){
            $content = $this->GetWeatherInfosFromCoordinate($content["latitude"], $content["longitude"]);
        }
        return $content;
    }

    function GetWeatherInfosFromCoordinate(string $x, string $y) : array {

        $requete = $this->client->request(
            'GET',
            'https://api.open-meteo.com/v1/forecast?latitude='.$x.'&longitude='.$y.'&hourly=temperature_2m,rain,cloud_cover,wind_speed_10m'
        );
        $content = $this->ExtractWeatherContentFromApi($requete->getContent());
        
        return $content;
    }

    function ExtractWeatherContentFromApi(string $content) : array {   
        
        $result = [];
        
        $result["is_empty"] = false;
        
        if(strlen(trim($content))>0){
            $content = json_decode($content, true);
            if(array_key_exists("hourly",$content)){
                $contentTemp = $content["hourly"];
                
                $contentTime = array_flip($contentTemp["time"]);
                
                foreach($contentTime as $key => $value){
                    $result[$key] = [
                        "temperature" => $contentTemp["temperature_2m"][$value],
                        "rain" => $contentTemp["rain"][$value],
                        "cloud" => $contentTemp["cloud_cover"][$value],
                        "wind" => $contentTemp["wind_speed_10m"][$value],
                        ];
                }
                
            }else{
                $result["is_empty"] = true;
        }
        }else{
            $result["is_empty"] = true;
        }
        return $result;
    }
    
    public function ExtractCityContentFromApi(string $content): array
    {   
        
        $result = [];
        $result["is_empty"] = false;
        if(strlen(trim($content))>0){
            $content = json_decode($content, true);
            if(array_key_exists("results",$content)){
                $result["city_name"] = $content["results"][0]["name"];
                $result["latitude"] = $content["results"][0]["latitude"];
                $result["longitude"] = $content["results"][0]["longitude"];
                $result["country"] = $content["results"][0]["country"];
                $result["country_code"] = $content["results"][0]["country_code"];
            }else{
                $result["is_empty"] = true;
        }
        }else{
            $result["is_empty"] = true;
        }
        return $result;
    }
}