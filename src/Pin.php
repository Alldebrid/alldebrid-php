<?php

namespace Alldebrid;

class Pin {

	public $alldebrid;
	
	public $pin;
	public $check;
	public $expiresIn;

	public function __construct(Alldebrid $alldebrid) {
		$this->alldebrid = $alldebrid;
	}

	public function code() {
		[ $response, $error ] = $this->alldebrid->pinGet();

		if($error) return $this->alldebrid->response($response, $error);

		$this->pin = $response['pin'];
		$this->check = $response['check'];
		$this->expiresIn = $response['expires_in'];
		$this->alldebrid->pinExpiration = $this->expiresIn;

		return $this->alldebrid->response($this->pin);
	}
	
	public function check() {
		[ $response, $error ] = $this->alldebrid->pinCheck($this->pin, $this->check);

		if($error) return $this->alldebrid->response($response, $error);

		if($response['activated'] != true) {
			$this->expiresIn = $response['expires_in'];
			$this->alldebrid->pinExpiration = $this->expiresIn;

			return $this->alldebrid->response(false);
		}

		$this->alldebrid->setApikey($response['apikey']);

		return $this->alldebrid->response(true);
	}
}