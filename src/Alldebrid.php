<?php

namespace Alldebrid;

class Alldebrid {

	public $apiUrl = 'https://api.alldebrid.com/v4/';
	
	public $agent;

	public $version = false;

	public $apikey = false;

	public $user = false;

	public $hostsCache = false;

	public $pin;
	public $pinExpiration = 0;

	public $options = [
		'autoInit' => false, // get user and host data on class instantiation
		'autoUnlockBestStreamQuality' => false, // auto choose best stream quality if unlock send stream list to choose from
		'ignoreRedirector' => true, // do not handle redirector
		'retry' => false, // retry failed request to API
		'maxRetries' => 2, // max 2 retries
		'exceptions' => false // emit exceptions
	];

	public function __construct($agent, $apikey = false) {
		// Agent is required
		$this->agent = $agent;

		if($apikey) {
			$this->apikey = $apikey;
			if($this->options['autoInit'] == true)
				$this->user();
		}

		if($this->options['autoInit'] == true)
			$this->hosts();
	}

	public function setApikey($apikey) {
		$this->apikey = $apikey;
		$this->pinReset();
	}

	public function response($data, $error = null) {
		if($error === null) {
			if($this->options['exceptions'] === true) {
				return $data;
			}
			return [ $data, null ];
		}

		if($this->options['exceptions'] === true) {
			throw new \Exception($error . ' : ' . $data);
		}

		return [ $data, $error ];
	}

	public function responseFlatten($response) {
		[ $data, $error ] = $response;
		return $this->response($data, $error);
	}

	public function apiError($error) {
		return $this->response($error['message'], $error['code']);
	}

	public function api($endpoint, $params = [], $retry = 0) {
		
		$contextOptions = [
			'http' => [
				'ignore_errors' => true,
				'timeout' => 10,
				'header' => []
			]
		];
		
		$params['agent'] = $this->agent;
		
		if($this->version !== false)
			$params['version'] = $this->version;

		// Send apikey in Authorization header
		if($this->apikey !== false) {
			$contextOptions['http']['header'][]  = 'Authorization: Bearer ' . $this->apikey;
		}
		
		$workingParams = $params;
		if(isset($params['POST'])) {
			// do POST shit, like file upload and stuff
			$contextOptions['http']['method'] = 'POST';
			
			if(isset($workingParams['POST']['FILES']) OR isset($workingParams['POST']['FILESPATH'])) {
				// File upload
				if(isset($workingParams['POST']['FILES'])) {
					$fileType = 'inline'; // file content
					$files = $workingParams['POST']['FILES'];
				} else {
					$fileType = 'path';  // file path
					$files = $workingParams['POST']['FILESPATH'];
				}

				$boundary = '--------------------------'.microtime(true);
				$contextOptions['http']['header'][] = 'Content-Type: multipart/form-data; boundary='. $boundary;

				$postData = '';
				$fileNo = 1;

				foreach($files AS $file) {
					if($fileType == 'path') {
						$fileContents = file_get_contents($file);
						$filename = basename($file);
					} else {
						$fileContents = $file;
						$filename = $fileNo . '.torrent';
						$fileNo++;
					}
					
					$postData .=  "--" . $boundary . "\r\n" . 
								"Content-Disposition: form-data; name=\"files[]\"; filename=\"" . $filename . "\"\r\n" . 
								"Content-Type: application/x-bittorrent\r\n\r\n" . 
								$fileContents . "\r\n";
				}

				// signal end of request (note the trailing "--")
				$postData .= "--" . $boundary . "--\r\n";

				//echo "Posting " . $postData . "\n\n";
			} else {
				// Standard POST
				$contextOptions['http']['header'][] = 'Content-Type: application/x-www-form-urlencoded';
				$postData = http_build_query($workingParams['POST'], null, '&');
				$postData = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '[]=', $postData);
			}

			$contextOptions['http']['content'] = $postData;

			unset($workingParams['POST']);
		}

		// https://stackoverflow.com/questions/8170306/http-build-query-with-same-name-parameters
		$query = http_build_query($workingParams, null, '&');
		$query = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '[]=', $query);

		$url = $this->apiUrl . $endpoint . '?' . $query;

		$context = stream_context_create($contextOptions);
		$rawResponse = file_get_contents($url, 0, $context);

		$response = json_decode($rawResponse, true);

		if(!$response OR !is_array($response) OR !isset($response['status'])) {
			if($this->options['retry'] === true AND $retry <= $this->options['maxRetries']) {
				sleep(1);
				$retry++;
				return $this->api($endpoint, $params, $retry);
			}
		}

		if($response['status'] == 'error' AND isset($response['error'])) {
			return $this->apiError($response['error']);
		}

		if($response['status'] == 'success' AND isset($response['data'])) {
			return [ $response['data'], null ];
		}

		return $this->response($response, 'BAD_RESPONSE');
	}


	private $hostsLastUpdate = 0;

	public function hosts() {
		if($this->hostsLastUpdate + 30 * 3600 < time()) {
			[ $response, $error ] = $this->api('user/hosts');

			if($error) return $this->response($response, $error);

			$this->hostsCache = $response;
			$this->hostsLastUpdate = time();
		}

		return $this->response($this->hostsCache);
	}

	public function hostsPriority() {
		return $this->response($this->api('hosts/priority'));
	}

	public function user() {

		[ $response, $error ] = $this->api('user');

		if($error) return $this->response($response, $error);

		$this->user = $response['user'];

		return $this->response($this->user);
	}

	public function userLinks() {
		[ $response, $error ] = $this->api('user/links');

		if($error) return $this->response($response, $error);

		return $this->response($response['links']);
	}

	public function userLinksSave($links) {
		if(!is_array($links))
			$links = [ $links ];

		return $this->responseFlatten($this->api('user/links/save', ['links' => $links]));
	}

	public function userLinksDelete($links) {
		if(!is_array($links))
			$links = [ $links ];

		return $this->responseFlatten($this->api('user/links/delete', ['links' => $links]));
	}

	public function userHistory() {
		// User history is enable ONLY if activated beforehand in the user account settings
		[ $response, $error ] = $this->api('user/history');

		if($error) return $this->response($response, $error);

		return $this->responseFlatten($response['links']);
	}

	public function userHistoryDelete() {
		return $this->responseFlatten($this->api('user/history/delete'));
	}


	public function magnet($magnet) {
		$magnet = new Magnet($this, $magnet);

		return $magnet;
	}
	
	public function magnetUpload($magnets) {
		if(!is_array($magnets))
			$magnets = [ $magnets ];

		if(count($magnets) > 1)
			[ $response, $error ] =  $this->api('magnet/upload', ['POST' => ['magnets' => $magnets]]);
		else
			[ $response, $error ] =  $this->api('magnet/upload', ['magnets' => $magnets]);

		if($error) return $this->response($response, $error);

		if(count($response['magnets']) == 1)
			return $this->response($response['magnets'][0]);

		return $this->response($response['magnets']);
	}

	public function magnetUploadFile($files, $method = 'file') {
		if(!is_array($files))
			$files = [ $files ];

		if($method == 'file')
			[ $response, $error ] =  $this->api('magnet/upload/file', ['POST' => ['FILESPATH' => $files]]);
		else if($method == 'inline')
			[ $response, $error ] =  $this->api('magnet/upload/file', ['POST' => ['FILES' => $files]]);
		else
			return $this->response('magnetUploadFile() method should be either file or inline', 'LIB_ERROR');

		if($error) return $this->response($response, $error);

		if(count($response['files']) == 1)
			return $this->response($response['files'][0]);

		return $this->response($response['files']);
	}

	public function magnetStatus($ids = false) {
		if($ids === false)
			[ $response, $error ] =  $this->api('magnet/status');
		else
		[ $response, $error ] =  $this->api('magnet/status', ['id' => $ids]);

		if($error) return $this->response($response, $error);

		return $this->response($response['magnets']);
	}

	public function magnetDelete($id) {
		return $this->responseFlatten($this->api('magnet/delete', ['id' => $id]));
	}

	public function magnetRestart($id) {
		return $this->responseFlatten($this->api('magnet/restart', ['id' => $id]));
	}

	public function magnetInstant($magnets) {
		if(!is_array($magnets))
			$magnets = [ $magnets ];

		return $this->responseFlatten($this->api('magnet/instant', ['magnets' => $magnets]));
	}



	public function link($url) {
		$link = new Link($this, $url);

		return $link;
	}

	public function linkType($links, $supportOnly = false) {
		$this->hosts();

		if($this->hostsCache === false) 
			return $this->response('COuld not retreive hosts data', 'LIB_NO_HOSTS');
			
		if(!is_array($links))
			$links = [ $links ];

		$result = [];

		foreach($links AS $link) {
			$hasMatched = false;
			foreach(['hosts', 'streams', 'redirectors'] AS $type) {
				if($type == 'redirectors' AND $this->options['ignoreRedirector'] == true)
					continue;

				foreach($this->hostsCache[$type] AS $service) {
					if(!isset($service['regexps']))

					if(!is_array($service['regexps']))
						$service['regexps'] = [ $service['regexps'] ];

					// 4th foreach, yeah baby...
					foreach($service['regexps'] AS $regexp) {
						if(preg_match('`'.$regexp.'`', $link)) {
							if($supportOnly === true)
								$result[] = true;
							else
								$result[] = $type;

							$hasMatched = true;
							break 3;
						}
					}
				}
			}
			if(!$hasMatched)
				$result[] = false;
		}

		if(count($result) == 1)
			return $this->response($result[0]);

		return $this->response($result);
	}

	public function linkIsSupported($links) {
		return $this->linkType($links, true);
	}


	public function linkInfos($links) {
		if(!is_array($links))
			$links = [ $links ];

		[ $response, $error ] = $this->api('link/infos', ['link' => $links]);

		if($error) return $this->response($response, $error);

		$infos = $response['infos'];

		if(count($infos) == 1)
			return $this->response($infos[0]);

		return $this->response($infos);
	}

	public function linkRedirector($link, $password = false) {
		[ $response, $error ] = $this->api('link/redirector', ['link' => $link, 'password' => $password]);

		if($error) return $this->response($response, $error);

		return $this->response($response['links']);
	}

	public function linkUnlock($link, $password = false) {
		[ $response, $error ] = $this->api('link/unlock', ['link' => $link, 'password' => $password]);

		if($error) return $this->response($response, $error);

		if(isset($response['streams']) AND count($response['streams']) > 1 AND $this->options['autoUnlockBestStreamQuality'] == true) {
			$bestStream = false;
			foreach($response['streams'] AS $stream) {
				if($stream['quality'] == 'mp3')
					continue;

				if($bestStream == false OR $stream['quality'] > $bestStream['quality'])
					$bestStream = $stream;
			}

			[ $response, $error ] = $this->api('link/streaming', ['id' => $response['id'], 'stream' => $bestStream['id']]);
		}

		return $this->response($response, $error);

	}

	public function linkStream($linkID, $streamID) {
		return $this->responseFlatten($this->api('link/streaming', ['id' => $linkID, 'stream' => $streamID]));
	}

	public function linkDelayed($delayedID) {
		return $this->responseFlatten($this->api('link/delayed', ['id' => $delayedID]));
	}

	public function linkWaitForDelayed($delayedID, $progressCallback = false) {
		do {
			sleep(5);
			[ $response, $error ] = $this->api('link/delayed', ['id' => $delayedID]);
			if($progressCallback !== false AND is_callable($progressCallback) AND $response['status'] != 2 AND !$error) {
				$progressCallback($response['time_left']);
			}
		} while(!$error AND $response['status'] != 2);
		
		return $this->response($response, $error);
	}

	public function pin() {
		$this->pin = new Pin($this);

		return $this->pin->code();
	}

	public function isLoggued() {
		if($this->pin instanceof Pin) {
			[ $response, $error ] = $this->pin->check();

			if($error) {
				if($error == 'PIN_EXPIRED')
					$this->pinReset();

				return $this->response($response, $error);
			}	
		}
		
		if($this->apikey != false) {
			return $this->response(true);
		}

		return $this->response(false);
	}

	public function waitForPin($progressCallback = false) {
		if(!($this->pin instanceof Pin)) {
			return $this->response('No pin() has been initialized', 'LIB_ERROR');
		}

		do {
			sleep(5);
			[ $isActivated, $error ] = $this->pin->check();

			if($progressCallback !== false AND is_callable($progressCallback) AND $isActivated != true AND !$error AND $this->pinExpiration > 0) {
				$progressCallback($this->pinExpiration);
			}
		} while(!$error AND $isActivated == false);

		return $this->response($isActivated, $error);
	}

	public function pinReset() {
		if($this->pin instanceof Pin) {
			$this->pin = null;
			$this->pinExpiration = 0;
		}
	}
	
	public function pinGet() {
		return $this->responseFlatten($this->api('pin/get'));
	}

	public function pinCheck($pin, $check) {
		return $this->responseFlatten($this->api('pin/check', ['pin' => $pin, 'check' => $check]));
	}

	public function error($code = 'GENERIC') {
		return $this->responseFlatten($this->api('error', ['code' => $code]));
	}
}