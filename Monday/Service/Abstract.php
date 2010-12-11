<?php

abstract class Monday_Service_Abstract
	implements Zend_Acl_Resource_Interface
{
	protected $_mapperName;
	protected $_mapper;
	protected $_identity;
	protected $_acl;
	protected $_aclResourceId;
	protected static $_defaultIdentity;
	protected static $_defaultAcl;

	public final function __construct($mapper = null, $identity = null,
		$acl = null)
	{
		if (is_string($this->_mapperName)) {
			$this->setMapper(Monday::mapper($this->_mapperName));
		} elseif ($mapper !== null) {
			$this->setMapper($mapper);
		}

		$this->setIdentity($identity);
		$this->setAcl($acl);
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

	public static function setDefaultIdentity($identity)
	{
		self::$_defaultIdentity = $identity;
	}

	public static function getDefaultIdentity()
	{
		return self::$_defaultIdentity;
	}

	public function setIdentity($identity)
	{
		$identity = !$identity ? self::getDefaultIdentity() : $identity;

		if ($identity instanceof Monday_Model) {
			$this->_identity = $identity;
		}

		return $this;
	}

	public function getIdentity()
	{
		return $this->_identity;
	}

	public static function setDefaultAcl(Zend_Acl $acl)
	{
		self::$_defaultAcl = $acl;
	}

	public static function getDefaultAcl()
	{
		return self::$_defaultAcl;
	}

	public function setAcl($acl)
	{
		$acl = !$acl ? self::getDefaultAcl() : $acl;

		// Only null or Zend_Acl allowed
		if ($acl instanceof Zend_Acl) {
			$this->_acl = $acl;
			$this->_setupAcl($this->_acl);
		}

		return $this;
	}

	public function getAcl()
	{
		return $this->_acl;
	}

	public function getResourceId()
	{
		if (empty($this->_aclResourceId)) {
			$message = "ACL not implemented for service '" .
				get_class($this) . "'";
			throw new Monday_Exception($message);
		}
		return $this->_aclResourceId;
	}

	/**
	 * Sets up service ACL, runs automatically
	 *
	 * @param Zend_Acl $acl ACL object
	 */
	protected function _setupAcl(Zend_Acl $acl)
	{
	}
}