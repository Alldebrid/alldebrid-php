<?php

namespace Alldebrid;

class Link {

	public $alldebrid;

	public $link;
	public $password = false;

	public $type;

	public $hasMultipleStreams = false;
	public $isDelayed = false;

	public $delayedID = 0;

	public $infos;
	public $unlockInfos;
	public $streamInfos;

	public function __construct(Alldebrid $alldebrid, $link, $password = false) {
		$this->alldebrid = $alldebrid;
		$this->link = $link;
	}
	
	public function type() {
		if($this->type !== null)
			return $this->alldebrid->response($this->type); // Already checked regexps

		[ $type, $error ] =  $this->alldebrid->linkType([$this->link]);

		if($error) return $this->alldebrid->response($type, $error);

		$this->type = $type;
		return $this->alldebrid->response($type);
	}

	public function isSupported() {
		[ $type, $error ] = $this->type();

		if($error) return $this->alldebrid->response($type, $error);

		if($type !== false)
			return $this->alldebrid->response(true);

		return $this->alldebrid->response(false);
	}

	public function infos() {
		[ $response, $error ] = $this->alldebrid->linkInfos([$this->link]);
		
		if($error) $this->alldebrid->response($response, $error);

		$this->infos = $response;

		return $this->alldebrid->response($response);
	}

	public function unlock() {
		[ $response, $error ] = $this->alldebrid->linkUnlock($this->link, $this->password);

		if($error) $this->alldebrid->response($response, $error);

		$this->unlockInfos = $response;

		if(isset($response['delayed'])) {
			$this->isDelayed = true;
			$this->delayedID = $response['delayed'];
		}
			

		if(isset($response['streams']) AND is_array($response['streams']) AND count($response['streams']) > 0)
			$this->hasMultipleStreams = true;

		return $this->alldebrid->response($response);
	}

	public function stream($streamID) {
		[ $response, $error ] =  $this->alldebrid->linkStream($this->unlockInfos['id'], $streamID);

		if($error) $this->alldebrid->response($response, $error);

		$this->streamInfos = $response;

		if(isset($response['delayed'])) {
			$this->isDelayed = true;
			$this->delayedID = $response['delayed'];
		}

		return $this->alldebrid->response($response);
	}

	public function delayed() {
		[ $response, $error ] =  $this->alldebrid->linkDelayed($this->delayedID);

		if($error) $this->alldebrid->response($response, $error);

		if($response['status'] == 2) {
			$this->isDelayed = false;
		}

		return $this->alldebrid->response($response);
	}

	public function waitFordelayed($progressCallback = false) {

		do {
			sleep(5);
			[ $response, $error ] =  $this->delayed();
			if($progressCallback !== false AND is_callable($progressCallback) AND $this->isDelayed === true AND !$error) {
				$progressCallback($response['time_left']);
			}
		} while(!$error AND $this->isDelayed === true);
		
		return $this->alldebrid->response($response, $error);
	}
}
