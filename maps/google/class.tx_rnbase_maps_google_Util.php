<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2015 Rene Nitzsche (rene@system25.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

tx_rnbase::load('tx_rnbase_maps_BaseMap');
tx_rnbase::load('tx_rnbase_util_Extensions');
tx_rnbase::load('tx_rnbase_util_Strings');
tx_rnbase::load('tx_rnbase_util_Logger');



/**
 * .
 */
class tx_rnbase_maps_google_Util
{

    /**
     * Possible options
     * - fullinfo: if FALSE (default) there is latitude and longitude returned only
     * - key: Google API key to be used.
     *
     * @param string $addressString
     * @param array $options fullInfo
     * @return array
     */
    public function lookupGeoCode($addressString, $options = [])
    {
    	$fullInfo = false;
    	if(is_array($options)) {
    		$fullInfo = isset($options['fullinfo']) ? $options['fullinfo'] : false;
    	}
    	else {
    		// backward compat
    		$fullInfo = $options;
    		$options = [];
    	}
    	$key = isset($options['key']) ? $options['key'] : '';

        $request = 'https://maps.googleapis.com/maps/api/geocode/json?address='.rawurlencode($addressString).'&key='.$key;
        $result = array();

        $time = microtime(true);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
        curl_close($curl);
        $requestTime = microtime(true) - $time;

        if ($requestTime > 2) {
            tx_rnbase_util_Logger::notice('Long request time for Google address lookup ', 'rn_base', array('uri' => $request, 'time' => $requestTime));
        }

        if ($response) {
            $response = json_decode($response, true);
            if ($response['status'] == 'OK') {
                if (!$fullInfo) {
                    $result = reset($response['results']);
                    $result = $result['geometry']['location'];
                } else {
                    $result = $response;
                }
            } elseif ($response['status'] == 'OVER_QUERY_LIMIT') {
                throw new Exception($response['error_message']);
            }
        }

        return $result;
    }
    /**
     *
     * @param string $street
     * @param string $zip
     * @param string $city
     * @param string $country
     * @param string $state
     * @throws Exception
     */
    public function lookupGeoCodeCached($street, $zip, $city, $country, $state = '')
    {
        if (!tx_rnbase_util_Extensions::isLoaded('wec_map')) {
            throw new Exception('wec_map not loaded');
        }

        return tx_wecmap_cache::lookup($street, $city, $state, $zip, $country);
    }
}
