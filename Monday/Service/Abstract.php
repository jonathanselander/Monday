<?php

abstract class Monday_Service_Abstract
{
	protected $_mapperName;
	protected $_mapper;

	public function __construct($mapper = null)
	{
		if (is_string($this->_mapperName)) {
			$this->setMapper(Monday::mapper($this->_mapperName));
		} elseif ($mapper !== null) {
			$this->setMapper($mapper);
		}
		$this->_init();
	}

	protected function _init()
	{
		// Implemented by sub-class
	}

	public function setMapper(Monday_Model_Mapper $mapper)
	{
		$this->_mapper = $mapper;

		return $this;
	}

	public function getMapper()
	{
		return $this->_mapper;
	}
}