<?php

/**
A class to wrap REST requests
*/
class RestRequest
{
	const REQ = 'REQUEST_METHOD';
	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
	const DEL = 'DELETE';

	private string $requestType;

	/**
	Initialize the Rest Request
	*/
	function __construct()
	{
		$this->requestType = $_SERVER[self::REQ];
	}

    /**
     * Returns the request variables
     * @throws Exception
     */
	function getRequestVariables(): array
	{
		$vars = [];

		//find the get variables
		if ($this->isGet()) {
			$vars = $_GET;
		} else if (count($_POST)) {
			$vars = $_POST;
		} else {
			//otherwise decode the post, put, or delete vars
			$input = file_get_contents('php://input');

			if(strlen($input))
			{
				$vars = json_decode($input, true);
			}

			//echo an error for debugging
			if (is_null($vars) && json_last_error()) {
				throw new Exception('JSON Error: '.json_last_error_msg());
			}
		}

		return $vars;
	}

	/**
	Returns the request type
	*/
	function getRequestType()
	{
		return $this->requestType;
	}

	/**
	Returns true if the request is GET
	*/
	function isGet(): bool
	{
		return $this->requestType === self::GET;
	}

	/**
	Returns true if the request is POST
	*/
	function isPost(): bool
	{
		return $this->requestType === self::POST;
	}

	/**
	Returns true if the request is PUT
	*/
	function isPut(): bool
	{
		return $this->requestType === self::PUT;
	}

	/**
	Returns true if the request is DELETE
	*/
	function isDelete(): bool
	{
		return $this->requestType === self::DEL;
	}
}
