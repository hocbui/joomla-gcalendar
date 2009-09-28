<?php
/**
 * GAnalytics is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GAnalytics is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GAnalytics.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Allon Moritz
 * @copyright 2007-2009 Allon Moritz
 * @version $Revision: 0.5.1 $
 */


class Analytics{

	private $user;
	private $password;
	private $profileID;
	private $parameters;
	private $startDate;
	private $endDate;

	private $data;
	private $authorization;
	private $map = array();

	/**
	 * public constructor
	 *
	 * @param string $sUser
	 * @param string $sPass
	 * @return analytics
	 */
	public function __construct($user, $pass){
		$this->user = $user;
		$this->password = $pass;
	}

	public function get($key) {
		return $this->map[$key];
	}

	public function put($key, $value) {
		$this->map[$key] = $value;
	}

	function getStartDate() {
		return $this->startDate;
	}

	function getEndDate() {
		return $this->endDate;
	}

	/**
	 * Sets the date range for GA data
	 *
	 * @param string $sStartDate (YYY-MM-DD)
	 * @param string $sEndDate   (YYY-MM-DD)
	 */
	public function setDateRange($sStartDate, $sEndDate){
		$this->data = null;
		$this->startDate = $sStartDate;
		$this->endDate   = $sEndDate;
	}

	function getParameters(){
		return $this->parameters;
	}

	function setParameters($dimension, $metrics, $max_results, $sort){
		$this->data = null;
		$parameters = array('dimensions' 	=> $dimension,
						'metrics'    	=> $metrics,
						'max-results'   => $max_results);
		if(!empty($sort))
		$parameters['sort'] = $sort;
		$this->parameters = $parameters;
	}

	public function setProfileById($sProfileId){
		$this->data = null;
		$this->profileID = $sProfileId;
	}

	public function getProfileList(){
		$this->auth();
		$xml = $this->getXml('https://www.google.com/analytics/feeds/accounts/default');
		return $this->parseAccountList($xml);
	}

	/**
	 * Parses GA XML to an array (dimension => metric)
	 * Check http://code.google.com/intl/nl/apis/analytics/docs/gdata/gdataReferenceDimensionsMetrics.html
	 * for usage of dimensions and metrics
	 *
	 * @return array result
	 */
	public function getData(){
		if($this->data == null){
			$this->auth();
			$params = array();
			foreach($this->parameters as $key => $property){
				$params[] = $key . '=' . urlencode($property);
			}

			$url = 'https://www.google.com/analytics/feeds/data?ids=' . $this->profileID .
                                                        '&start-date=' . date('Y-m-d', $this->startDate). 
                                                        '&end-date=' . date('Y-m-d', $this->endDate). '&' . 
			implode('&', $params);
			$xml = $this->getXml($url);
			$result = array();

			$doc = new DOMDocument();
			$doc->loadXML($xml);
			$entries = $doc->getElementsByTagName('entry');
			foreach($entries as $entry){
				$metrics = $entry->getElementsByTagName('metric');
				$dimensions = $entry->getElementsByTagName('dimension');
				$tmp = new AnalyticsItem();
				for ($i = 0; $i < $dimensions->length; $i++){
					$item = $dimensions->item( $i );
					$tmp->addDimension($item->getAttribute('name'), $item->getAttribute('value'));
				}
				for ($i = 0; $i < $metrics->length; $i++){
					$item = $metrics->item( $i );
					$tmp->addMetric($item->getAttribute('name'), $item->getAttribute('value'));
				}
				$result[] = $tmp;
			}
			$this->data = $result;
		}
		return $this->data;
	}

	

	private function auth(){
		if (isset($_SESSION['auth'])){
			$this->authorization = $_SESSION['auth'];
			return;
		}
		$aPost = array ( 'accountType'   => 'HOSTED_OR_GOOGLE',
                         'Email'         => $this->user,
                         'Passwd'        => $this->password,
                         'service'       => 'analytics',
                         'source'        => 'GAnalytics-com_ganalytics-0.5.1');
			
		$sResponse = $this->getUrl('https://www.google.com/accounts/ClientLogin', $aPost);

		$_SESSION['auth'] = '';
		if (strpos($sResponse, "\n") !== false){
			$aResponse = explode("\n", $sResponse);
			foreach ($aResponse as $sResponse){
				if (substr($sResponse, 0, 4) == 'Auth'){
					$_SESSION['auth'] = trim(substr($sResponse, 5));
				}
			}
		}
		if ($_SESSION['auth'] == ''){
			unset($_SESSION['auth']);
			throw new Exception('Retrieving Auth hash failed!');
		}
		$this->authorization = $_SESSION['auth'];
	}

	private function getXml($url){
		return $this->getUrl($url, array(), array('Authorization: GoogleLogin auth=' . $this->authorization));
	}

	/**
	 * Parse XML from account list
	 *
	 * @param string $sXml
	 */
	private function parseAccountList($sXml){
		$oDoc = new DOMDocument();
		$oDoc->loadXML($sXml);
		$oEntries = $oDoc->getElementsByTagName('entry');
		$i = 0;
		$aProfiles = array();
		foreach($oEntries as $oEntry){
			$aProfiles[$i] = array();

			$oTitle = $oEntry->getElementsByTagName('title');
			$aProfiles[$i]["title"] = $oTitle->item(0)->nodeValue;

			$oEntryId = $oEntry->getElementsByTagName('id');
			$aProfiles[$i]["entryid"] = $oEntryId->item(0)->nodeValue;

			$oProperties = $oEntry->getElementsByTagName('property');
			foreach($oProperties as $oProperty){
				if (strcmp($oProperty->getAttribute('name'), 'ga:accountId') == 0){
					$aProfiles[$i]["accountId"] = $oProperty->getAttribute('value');
				}
				if (strcmp($oProperty->getAttribute('name'), 'ga:accountName') == 0){
					$aProfiles[$i]["accountName"] = $oProperty->getAttribute('value');
				}
				if (strcmp($oProperty->getAttribute('name'), 'ga:profileId') == 0){
					$aProfiles[$i]["profileId"] = $oProperty->getAttribute('value');
				}
				if (strcmp($oProperty->getAttribute('name'), 'ga:webPropertyId') == 0){
					$aProfiles[$i]["webPropertyId"] = $oProperty->getAttribute('value');
				}
			}
			$oTableId = $oEntry->getElementsByTagName('tableId');
			$aProfiles[$i]["tableId"] = $oTableId->item(0)->nodeValue;
			$i++;
		}
		return $aProfiles;
	}

	/**
	 * Get data from given URL
	 * Uses Curl if installed, falls back to file_get_contents if not
	 *
	 * @param string $sUrl
	 * @param array $aPost
	 * @param array $aHeader
	 * @return string Response
	 */
	private function getUrl($sUrl, $aPost = array(), $aHeader = array()){
		if (count($aPost) > 0){
			// build POST query
			$sMethod = 'POST';
			$sPost = http_build_query($aPost);
			$aHeader[] = 'Content-type: application/x-www-form-urlencoded';
			$aHeader[] = 'Content-Length: ' . strlen($sPost);
			$sContent = $aPost;
		} else {
			$sMethod = 'GET';
			$sContent = null;
		}

		if (function_exists('curl_init')){
			// If Curl is installed, use it!
			$rRequest = curl_init();
			curl_setopt($rRequest, CURLOPT_URL, $sUrl);
			curl_setopt($rRequest, CURLOPT_RETURNTRANSFER, 1);

			if ($sMethod == 'POST'){
				curl_setopt($rRequest, CURLOPT_POST, 1);
				curl_setopt($rRequest, CURLOPT_POSTFIELDS, $aPost);
			} else {
				curl_setopt($rRequest, CURLOPT_HTTPHEADER, $aHeader);
			}

			$sOutput = curl_exec($rRequest);
			if ($sOutput === false){
				throw new Exception('Curl error (' . curl_error($rRequest) . ')');
			}

			$aInfo = curl_getinfo($rRequest);

			if ($aInfo['http_code'] != 200){
				// not a valid response from GA
				if ($aInfo['http_code'] == 400){
					throw new Exception('Bad request (' . $aInfo['http_code'] . ') url: ' . $sUrl);
				}
				if ($aInfo['http_code'] == 403){
					throw new Exception('Access denied (' . $aInfo['http_code'] . ') url: ' . $sUrl);
				}
				throw new Exception('Not a valid response (' . $aInfo['http_code'] . ') url: ' . $sUrl);
			}
			curl_close($rRequest);
		} else {
			// Curl is not installed, use file_get_contents
			// create headers and post
			$aContext = array('http' => array ( 'method' => $sMethod,
                                                'header'=> implode("\r\n", $aHeader) . "\r\n",
                                                'content' => $sContent));
			$rContext = stream_context_create($aContext);

			$sOutput = @file_get_contents($sUrl, 0, $rContext);
			if (strpos($http_response_header[0], '200') === false){
				// not a valid response from GA
				throw new Exception('Not a valid response (' . $http_response_header[0] . ') url: ' . $sUrl);
			}
		}
		return $sOutput;
	}
}

class AnalyticsItem{

	private $dimensions;
	private $metrics;

	public function __construct(){
		$this->dimensions = array();
		$this->metrics = array();
	}

	public function getAvailableDimensionNames(){
		return array_keys($this->dimensions);
	}

	public function getDimension($dimensionName){
		return $this->dimensions[$dimensionName];
	}

	public function addDimension($dimensionName, $dimensionValue){
		$this->dimensions[$dimensionName] = $dimensionValue;
	}

	public function getAvailableMetricNames(){
		return array_keys($this->metrics);
	}

	public function getMetric($metricName){
		return $this->metrics[$metricName];
	}

	public function addMetric($metricName, $metricValue){
		$this->metrics[$metricName] = $metricValue;
	}
}
?>