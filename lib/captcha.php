<?php

namespace OCA\Tenant_Portal;
use \OCA\Tenant_Portal\Util;

class Captcha {
	const API_URL = "https://www.google.com/recaptcha/api/siteverify";
	const API_SECRET= "6Lcv2SMUAAAAAMeoG-QRV_mncvDWVdozP3Mg0UM_"; // TODO: Pull this out to the config file
	private $token;

	/**
	 * @param string $token
	 */
	public function __construct($token) {
		$this->token = $token;
	}

	/**
	 * Validate the captcha token
	 * @return boolean
	 */
	public function validate() {
		$data = Array(
			"secret" => self::API_SECRET,
			"response" => $this->token
		);
		$validation_response = $this->post(self::API_URL, $data);
		try {
			$json = json_decode($validation_response, true);
			if ($json["success"] > 0) {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			return false;
		}
		return false;
	}

	/**
	 * Performs a POST to a URL
	 * @param string $url
	 * @param Array $data
	 */
	private function post($url, $data) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}
}

