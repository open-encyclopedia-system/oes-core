<?php
/*
 * This file is part of OES, the Open Encyclopedia System.
 *
 * Copyright (C) 2020 Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>

<?php

class Oes_LocationServices_Ctrl {

    // 52.499827 / 13.4249657
    // curl --request GET   --url 'https://us1.locationey=afae0f36d68c49&lat=52.499&lon=13.424&tag=railway_station&radius=1000&format=json'

    // https://github.com/location-iq/leaflet-geocoder


    /**
     * @param $zipcode
     * @param string $country
     * @param bool $asobject
     * @return array|dtm_geofeat|mixed
     * @throws Exception
     */
    function lookupZipcodeLocation($zipcode,$country='DE',$asobject=false)
    {

            $geofeatzipcodeid = oes_wp_query_first_post_id(dtm_geofeat::POST_TYPE, [
                [
                    'key' => dtm_geofeat::attr_name,
                    'value' => $zipcode
                ],
                [
                    'key' => dtm_geofeat::attr_country,
                    'value' => $country
                ]

            ]);

            $geofeat = dtm_geofeat::init($geofeatzipcodeid);

            if ($asobject) {
                return $geofeat;
            } else {
                return [$geofeat->lat, $geofeat->lng];
            }

    }

    function lookupLocations($query)
    {
        $curl = curl_init('https://us1.locationiq.com/v1/search.php?key='.Aufstehen_Config::LOCATIONIQ_APIKEY.'&q='.urlencode($query).'&format=json&accept-language=de');

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER    =>  true,
            CURLOPT_FOLLOWLOCATION    =>  true,
            CURLOPT_MAXREDIRS         =>  10,
            CURLOPT_TIMEOUT           =>  30,
            CURLOPT_CUSTOMREQUEST     =>  'GET',
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new Exception($err);
        } else {
            return json_decode($response, true);
        }
    }

    /**
     * @param $lat
     * @param $lng
     * @return Oes_Loc_Location
     */
    function reverseGeoCode($lat,$lng)
    {

        $locqueryresult = file_get_contents("https://eu1.locationiq.com/v1/reverse.php?key=".Aufstehen_Config::LOCATIONIQ_APIKEY."&lat=$lat&lon=$lng&format=json&accept-language=de");

        $qlocation = json_decode($locqueryresult,true);

        $addr = $qlocation['address'];
        $city_district = $addr['city_district'];
        $city  = $addr['city'];
        $state = $addr['state'];
        if (empty($city)) {
            $city = $state;
        }
        $county  = $addr['county'];
        $countrycode  = strtoupper($addr['country_code']);
        $country  = $addr['country'];
        $zipcode  = $addr['postcode'];
        $suburb  = $addr['suburb'];
        $neighbourhood  = $addr['neighbourhood'];
        $addr_display_name  = $qlocation['display_name'];

        $normalizecity = [
            'city', 'locality', 'town', 'borough', 'municipality', 'village', 'state'];

        $loc = new Oes_Loc_Location();

        foreach ($normalizecity as $prop)
        {
            $val = $addr[$prop];
            if (!empty($val)) {
                $city = $val;
                break;
            }
        }

        $pedes = $qlocation['address']['pedestrian'];
        $road = $qlocation['address']['road'];

        $street = !empty($road) ? $road : (!empty($pedes) ? $pedes : '');

        $loc->street = $street;
        $loc->housenumber = $qlocation['address']['house_number'];
        $loc->district = $city_district;
        $loc->suburb = $suburb;
        $loc->neighbourhood = $neighbourhood;
        $loc->data = $qlocation;
        $loc->city = $city;
        $loc->county = $county;
        $loc->state = $state;
        $loc->country_name = $country;
        $loc->country_code = $countrycode;
        $loc->zipcode = $zipcode;
        $loc->osm_type = $qlocation['osm_type'];
        $loc->osm_id = $qlocation['osm_id'];
        $loc->lat = $qlocation['lat'];
        $loc->lng = $qlocation['lon'];

        return $loc;
        
    }

}

class Oes_Loc_Location {

    var $lat, $lng;
    
    var $street, $housenumber, $city, $neighbourhood,
        $county, $state, $country_name, $country_code, $zipcode, $district, $suburb;

    var $display_name, $data;

    var $osm_type, $osm_id;

}