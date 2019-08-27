<?php
namespace App\Tools;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

class Tools {

	public static function curl_get($url, $timeout) {
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_URL, $url);
		curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $timeout);
		#curl_setopt($ci, CURLOPT_USERAGENT, 'PHP/'.PHP_VERSION);
		curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ci, CURLOPT_SSL_VERIFYHOST,0);  
		curl_setopt ($ci, CURLOPT_SSL_VERIFYPEER,0);  
		$ret = curl_exec($ci);
		$httpcode = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		curl_close($ci);
		if ($httpcode >= 400) {
			throw new \Exception(sprintf("curl_get from %s return %d.", $url, $httpcode));
		}

		return $ret;
	}

	public static function curl_post($url, $data, $timeout) {
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_URL, $url);
		curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $timeout);
		#curl_setopt($ci, CURLOPT_USERAGENT, 'PHP/'.PHP_VERSION);
		curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ci, CURLOPT_POST,1);
		if (is_array($data)) {
			curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
		} else {
			curl_setopt($ci, CURLOPT_POSTFIELDS, $data);
		}

		$ret = curl_exec($ci);
		$httpcode = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		curl_close($ci);
		if($httpcode >= 400) {
			throw new Exception(sprintf("curl_post from %s return %d.", $url, $httpcode));
		}

		return $ret;
	}



	
}

?>
