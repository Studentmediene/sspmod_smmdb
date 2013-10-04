<?php
/**
 * Authentication source for smmdb (Student Mediene MedlemsDataBase)
 *
 * Uses the REST API.
 *
 * Requires the following configuration directives:
 * 	smmdb_host string hostname smmdb listens on
 * 	smmdb_port int port number smmdb listens on
 * 	smmdb_insecure bool true if smmdb listens on insecure http (opposed to secure https)
 */
require_once dirname(__FILE__) . '/httpful.phar';

use \Httpful\Request;
use \Httpful\Mime;
use \Httpful\Exception\ConnectionErrorException;

class sspmod_smmdb_Auth_Source_Rest extends sspmod_core_Auth_UserPassBase {
	private $smmdb_host;
	private $smmdb_port;
	private $smmdb_api_key;
	private $smmdb_insecure;
	private $smmdb_timeout;

	/**
	 * inherits from sspmod_core_Auth_UserPassBase
	 *
	 * @param $info @see sspmod_core_Auth_UserPassBase
	 * @param $config @see sspmod_core_Auth_UserPassBase,
	 *	requires string "smmdb_host" @see #getSmmdbHost()
	 *	supports string "smmdb_port" @see #getSmmdbPort()
	 *	supports boolean "smmdb_insecure" @see #isInsecure()
	 * 	requires string "smmdb_api_key" @see #getApiKey()
	 */
	public function __construct($info, $config) {
		parent::__construct($info, $config);
		if (!isset($config['smmdb_port']))
			$config['smmdb_port'] = 0;
		assert('!preg_replace("_[0-9]_", "", $config["smmdb_port"])', 'smmdb_port is not an integer');
		assert('(int)$config["smmdb_port"] <= 65535 && (int)$config["smmdb_port"] >= 0', 'smmdb_port out of bounds');
		assert('is_string($config["smmdb_host"]);');
		assert('is_string($config["smmdb_api_key"]);');
		assert('!preg_replace("_[0-9]_", "", $config["smmdb_timeout"])', 'smmdb_timeout is not an integer');
		assert('(int)$config["smmdb_timeout"] <= 60 && (int)$config["smmdb_timeout"] > 0', 'smmdb_timeout out of bounds');
		$this->smmdb_host = ''.$config['smmdb_host'];
		$this->smmdb_port = (int)$config['smmdb_port'];
		$this->smmdb_insecure = isset($config['smmdb_insecure']) && $config['smmdb_insecure'];
		$this->smmdb_api_key = ''.$config['smmdb_api_key'];
		$this->smmdb_timeout = $config['smmdb_timeout'];
	}

	/**
	 * String indicating the hostname where smmdb runs
	 * @return string smmdb hostname
	 */
	public function getSmmdbHost() {
		return $this->smmdb_host;
	}
	/**
	 * Integer indicating the portnumber smmdb runs on
	 * @return int smmdb port number
	 */
	public function getSmmdbPort() {
		return $this->smmdb_port;
	}
	/**
	 * Api for using the smmdb REST api
	 * @return string smmdb api key
	 */
	public function getApiKey() {
		return $this->smmdb_api_key;
	}
	/**
	 * Boolean to indicate whether to use http (true) or https (false)
	 * @return bool smmdb runs on insecure http
	 */
	public function isInsecure() {
		return $this->smmdb_insecure;
	}
	/**
	 * Integer which indicates how long to wait for an answer from smmdb.
	 * @return int seconds to wait for reply
	 */
	public function getTimeout() {
		return $this->smmdb_timeout;
	}

	/**
	 * Generate a url
	 * @param $host string hostname in URL part
	 * @param $port int port number in URL part
	 * @param $path string path in URL part
	 * @param $insecure bool protocol to use (1=http/0=https)
	 */
	protected static function generateUri($host, $port, $apiKey, $path, $insecure=false) {
		return 'http'.($insecure?'':'s').'://'.rawurlencode($host).($port?':'.$port:'').'/api/'.ltrim($path,'/').'?key='.rawurlencode($apiKey);
	}

	/**
	 * Attempt login, takes login credentials username and password
	 * and returns whether smmdb accepts these.
	 * @param $username string user provided username
	 * @param $password string user provided password
	 * @return bool the credentials were accepted by smmdb
	 */
	protected function login($username, $password) {
		$path = 'user/by_username/'.rawurlencode($username).'/check_password';
		$uri = $this->generateUri($this->getSmmdbHost(), $this->getSmmdbPort(), $this->getApiKey(), $path, $this->isInsecure());

		try {
			$logonAttempt = Request::post($uri)
				->body('password='.rawurlencode($password))
				->expectsType('application/json')
				->sendsType(Mime::FORM)
				->timeout($this->getTimeout())
				->send();
		} catch (ConnectionErrorException $e) {
			throw new SimpleSAML_Error_Error('WRONGUSERPASS');
		}

		if (!isset($logonAttempt->body->correct_password) || !$logonAttempt->body->correct_password)
			throw new SimpleSAML_Error_Error('WRONGUSERPASS');

		return $this->fetchUserData($username);
	}

	/**
	 * Retrieve user data from smmdb for one user.
	 * This function assumes that the user has been logged in correctly,
	 * and should only be called from #login(string,string)
	 * @param $username string user provided name
	 * @return array[] userdata returned by smmdb
	 */
	private function fetchUserData($username) {
		$path = 'user/by_username/'.rawurlencode($username);
		$uri = $this->generateUri($this->getSmmdbHost(), $this->getSmmdbPort(), $this->getApiKey(), $path, $this->isInsecure());

		try {
			$userDataRequest = Request::get($uri)
				->expectsType('application/json')
				->sendsType(Mime::FORM)
				->timeout($this->getTimeout())
				->send();
		} catch (ConnectionErrorException $e) {
			throw new SimpleSAML_Error_Error('WRONGUSERPASS');
		}

		if ($userDataRequest->body) {
			$result = get_object_vars($userDataRequest->body);
			foreach($result as $key => &$value) {
				if (!is_array($value))
					$value = array($value);
			}
			return $result;
		}
		throw new SimpleSAML_Error_Error('WRONGUSERPASS');
	}
}

