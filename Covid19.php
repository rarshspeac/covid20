<?php
/*
.---------------------------------------------------------------------------.
|    Script: Corona Virus COVID-19                                          |
|   Version: 1.3                                                            |
|   Release: March 16, 2020 (10:19 WIB)                                     |
|    Update: March 22, 2020 (21:57 WIB)                                     |
|                                                                           |
|                     Pasal 57 ayat (1) UU 28 Tahun 2014                    |
|      Copyright © 2019, Afdhalul Ichsan Yourdan. All Rights Reserved.      |
| ------------------------------------------------------------------------- |
| Hubungi Saya:                                                             |
| - Facebook    - Afdhalul Ichsan Yourdan   - https://s.id/ShennFacebook    |
| - Instagram   - ShennBoku                 - https://s.id/ShennInstagram   |
| - Telegram    - ShennBoku                 - https://t.me/ShennBoku        |
| - Twitter     - ShennBoku                 - https://s.id/ShennTwitter     |
| - WhatsApp    - 0878 7954 2355            - 0822 1158 2471                |
'---------------------------------------------------------------------------'
*/

class Covid19
{
    public function globals() {
        $try = $this->connect('https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/ncov_cases/FeatureServer/2/query?f=json&where=Confirmed%20%3E%200&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&orderByFields=Confirmed%20desc');
        if(isset($try['features'][0]['attributes']['Country_Region'])) {
            date_default_timezone_set('Asia/Jakarta');
            $Confirmed=0;$Recovered=0;$Deaths=0;$Cares=0;$ranks=1;
            for($i = 0; $i <= count($try['features'])-1; $i++) {
                $data = $try['features'][$i]['attributes'];
                $out[] = [
                    'ranks' => $ranks,
                    'country' => $data['Country_Region'],
                    'updated_at' => date('Y-m-d H:i:s', substr($data['Last_Update'],0,10)),
                    'confirmed' => $data['Confirmed'],
                    'recovered' => $data['Recovered'],
                    'deaths' => $data['Deaths'],
                    'cares' => $data['Active'],
                ];
                $Confirmed+=$data['Confirmed'];$Recovered+=$data['Recovered'];$Deaths+=$data['Deaths'];$Cares+=$data['Active'];$ranks++;
            }
            for($o = 0; $o <= count($out)-1; $o++) { $country[] = $out[$o]['country']; }
            return ['result' => true,'data' => [
                'confirmed' => $Confirmed,
                'recovered' => $Recovered,
                'deaths' => $Deaths,
                'cares' => $Cares,
                'list' => $country,
                'all' => $out
            ],'message' => 'Data successfully obtained.'];
        } else {
            if(isset($try['error']['message'])) {
                return ['result' => false,'data' => null,'message' => $try['error']['message']];
            } else {
                return ['result' => false,'data' => null,'message' => 'Failed to get data.'];
            }
        }
    }
    
    public function local() {
        $try = $this->connect('https://services5.arcgis.com/VS6HdKS0VfIhv8Ct/arcgis/rest/services/COVID19_Indonesia_per_Provinsi/FeatureServer/0/query?where=1%3D1&outFields=*&outSR=4326&orderByFields=Kasus_Posi+DESC&f=json');
        if(isset($try['features'][0]['attributes']['Provinsi'])) {
            date_default_timezone_set('Asia/Jakarta');
            $Confirmed=0;$Recovered=0;$Deaths=0;$Cares=0;$ranks=1;
            for($i = 0; $i <= count($try['features'])-1; $i++) {
                $data = $try['features'][$i]['attributes'];
                $caresRE = $data['Kasus_Posi'] - $data['Kasus_Semb'] - $data['Kasus_Meni'];
                $out[] = [
                    'ranks' => $ranks,
                    'province' => $data['Provinsi'],
                    'updated_at' => date('Y-m-d H:i:s'),
                    'confirmed' => $data['Kasus_Posi'],
                    'recovered' => $data['Kasus_Semb'],
                    'deaths' => $data['Kasus_Meni'],
                    'cares' => $caresRE,
                ];
                $Confirmed+=$data['Kasus_Posi'];$Recovered+=$data['Kasus_Semb'];$Deaths+=$data['Kasus_Meni'];$Cares+=$caresRE;$ranks++;
            }
            for($o = 0; $o <= count($out)-1; $o++) { $province[] = $out[$o]['province']; }
            return ['result' => true,'data' => [
                'confirmed' => $Confirmed,
                'recovered' => $Recovered,
                'deaths' => $Deaths,
                'cares' => $Cares,
                'list' => $province,
                'all' => $out
            ],'message' => 'Data successfully obtained.'];
        } else {
            if(isset($try['error']['message'])) {
                return ['result' => false,'data' => null,'message' => $try['error']['message']];
            } else {
                return ['result' => false,'data' => null,'message' => 'Failed to get data.'];
            }
        }
    }
    
    public function indonesia() {
        $try = $this->connect('https://kawalcovid19.harippe.id/api/summary');
        if(isset($try['confirmed']['value'])) {
            $date = substr(str_replace('T',' ',$try['metadata']['lastUpdatedAt']),0,19);
            return ['result' => true,'data' => [
                'updated_at' => date('Y-m-d H:i:s', strtotime('+7 hours', strtotime($date))),
                'confirmed' => $try['confirmed']['value'],
                'recovered' => $try['recovered']['value'],
                'deaths' => $try['deaths']['value'],
                'cares' => $try['activeCare']['value']
            ],'message' => 'Data berhasil didapatkan'];
        } else {
            return ['result' => false,'data' => null,'message' => 'Gagal mendapatkan data'];
        }
    }

    # END POINT CONNECTION #

    private function connect($point) {
        $json_result = json_decode(file_get_contents($point), true);
        if(!$json_result) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE); 
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.47 Safari/537.36');
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $point);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $chresult = curl_exec($ch);
            curl_close($ch);
            $json_result = json_decode($chresult, true);
        }
        return $json_result;
    }
}

$Covid19 = new Covid19;
// header('Content-Type: application/json');
// print json_encode($Covid19->globals(), JSON_PRETTY_PRINT);
// print json_encode($Covid19->local(), JSON_PRETTY_PRINT);
// print json_encode($Covid19->indonesia(), JSON_PRETTY_PRINT);