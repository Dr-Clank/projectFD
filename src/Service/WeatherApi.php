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

    /**
     * Récupère les informations d'une ville comme ces coordonnées et son pays
     *
     * @param string $cityName  Nom de la ville a recherché
     * 
     * @return array Retourne un tableau de valeurs contenant la villes et ces coordonnées. 
     * Si aucune ne sont trouvé renvoie un tableau avec ["is_empty" => true]
     */ 
    function getCityInfos(string $cityName) : array {
        
        $cityName = str_replace(" ", "-", $cityName );
        

        $requete = $this->client->request(
            'GET',
            'https://geocoding-api.open-meteo.com/v1/search?name='.$cityName.'&count=1&language=en&format=json'
        );
        $content = $this->ExtractCityContentFromApi($requete->getContent());
        return $content;
    }

    /**
     * Récupère les informations métérologique d'une ville à partir du nom de la ville
     *
     * @param string $cityName  Nom de la ville a recherché
     * 
     * @return array Retourne un tableau contenant pour chaque heure la température, le vent, l'humidité (pluie) et la densité nuageuse
     * Si aucune ne sont trouvé renvoie un tableau avec ["is_empty" => true]
     */ 
    function getWeatherInfosFromCity(string $cityName) : array {

        $content = $this->getCityInfos($cityName);
        if($content["is_empty"] != true){
            $content = $this->getWeatherInfosFromCoordinate($content["latitude"], $content["longitude"]);
        }
        return $content;
    }

    /**
     * Récupère les informations métérologique d'une paire de coordonnées
     *
     * @param string $x  Latitude
     * @param string $y  Longitude
     * 
     * @return array Retourne un tableau contenant pour chaque heure la température, le vent, l'humidité (pluie) et la densité nuageuse
     * Si aucune ne sont trouvé renvoie un tableau avec ["is_empty" => true]
     */ 
    function getWeatherInfosFromCoordinate(string $x, string $y) : array {

        $requete = $this->client->request(
            'GET',
            'https://api.open-meteo.com/v1/forecast?latitude='.$x.'&longitude='.$y.'&hourly=temperature_2m,rain,cloud_cover,wind_speed_10m'
        );
        $content = $this->ExtractWeatherContentFromApi($requete->getContent());
        
        return $content;
    }

    /**
     * Extrait les informations métérologiques nécéssaires à partir du contenue de la requêtes
     *
     * @param string $content   Contenue Json retourné par une requête
     * 
     * @return array Retourne un tableau contenant pour chaque heure la température, le vent, l'humidité (pluie) et la densité nuageuse
     * Si aucune ne sont trouvé renvoie un tableau avec ["is_empty" => true]
     */
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
    
    /**
     * Extrait les informations d'une ville nécéssaires à partir du contenue de la requêtes
     *
     * @param string $content   Contenue Json retourné par une requête
     * 
     * @return array Retourne un tableau contenant le nom, les coordonnées et le pays où se trouve la ville.
     * Si aucune ne sont trouvé renvoie un tableau avec ["is_empty" => true]
     */
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

    function getDailyAverages($hourlyData) {
        $result = [];

        // Heures fixes pour lesquelles on veut la moyenne
        $targetHours = ['00:00', '06:00', '12:00', '18:00'];

        foreach ($hourlyData as $datetime => $data) {
            // On sépare la date et l'heure
            $date = substr($datetime, 0, 10); // YYYY-MM-DD
            $hour = substr($datetime, 11, 5); // HH:MM

            // On ne garde que les heures cibles
            if (in_array($hour, $targetHours)) {
                if (!isset($result[$date][$hour])) {
                    $result[$date][$hour] = ['temperature' => 0, 'rain' => 0, 'cloud' => 0, 'wind' => 0, 'count' => 0];
                }

                // On accumule les valeurs
                $result[$date][$hour]['temperature'] += $data['temperature'];
                $result[$date][$hour]['rain'] += $data['rain'];
                $result[$date][$hour]['cloud'] += $data['cloud'];
                $result[$date][$hour]['wind'] += $data['wind'];
                $result[$date][$hour]['count'] += 1;
            }
        }

        // Calcul des moyennes
        foreach ($result as $date => $hours) {
            foreach ($hours as $hour => $values) {
                $count = $values['count'];
                if ($count > 0) {
                    $result[$date][$hour]['temperature'] = round($values['temperature'] / $count, 1);
                    $result[$date][$hour]['rain'] = round($values['rain'] / $count, 1);
                    $result[$date][$hour]['cloud'] = round($values['cloud'] / $count, 1);
                    $result[$date][$hour]['wind'] = round($values['wind'] / $count, 1);
                }

                // On peut retirer le compteur pour simplifier le tableau final
                unset($result[$date][$hour]['count']);
            }
        }

        return $result;
    }

}