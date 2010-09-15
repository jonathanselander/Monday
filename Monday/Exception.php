<?php

class Monday_Exception extends Exception
{
	protected $_errorMessages = array();
	
	public function addErrorMessage($error)
	{
		$this->_errorMessages[] = $error;
	}
	
	public function setErrorMessages($errors)
	{
		$this->_errorMessages = $errors;
		
		return $this;
	}
	
	public function getErrorMessages()
	{
		return $this->_errorMessages;
	}
}