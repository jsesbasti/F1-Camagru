<?php

class Response implements JsonSerializable {
	private $_response;

	public function __construct(array $response) {
		$this->_response = $response;
	}

	public function jsonSerialize(): mixed {
		return $this->_response;
	}
}

?>