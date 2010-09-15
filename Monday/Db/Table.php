<?php

class Monday_Db_Table extends Zend_Db_Table_Abstract
{
	protected $_rowClass = 'Monday_Db_Table_Row';
	protected $_protectedTable = false;
	protected static $_useProtection = false;
	
	public function __call($function, $args)
	{
		switch (true) {
			case preg_match('/^fetchBy[A-Z]/', $function):
				$field = preg_replace('/^fetchBy/', '', $function);
				$field = Monday_Model::functionToField($field);
				return $this->fetchSpecificField($field, $args);
				break;
		}
		
		throw new Exception('Incorrect function call');
	}
	
	public function getPrimary()
	{
		return $this->_primary;
	}
	
	public function fetchSpecificField($field, $args)
	{
		if (count($args) === 0) {
			throw new Exception('Field value must be supplied');
		}
		
		$value = $args[0];
		$operator = !empty($args[1]) ? $args[1] : '=';
		
		$field = $this->getAdapter()
			->quoteIdentifier($field);
		
		$where = $this->getAdapter()
			->quoteInto("$field $operator ?", $value);
		
		$select = $this->select()
			->where($where);
			
		if (!empty($args[2])) {
			$select->order($args[2]);
		}
		
		return $this->fetchAll($select);
	}
	
	
	public function getFields()
	{
		$info = $this->info();
		
		foreach ($info['cols'] as $col) {
			$values[$col] = null;
		}
		
		return $values;
	}
	
	public function fetchAll($where = null, $order = null, $count = null, $offset = null)
	{
		$where = $this->_appendProtectedCondition($where);
		
		return parent::fetchAll($where, $order, $count, $offset);
	}
	
	protected function _appendProtectedCondition($where)
	{
		if (self::getUseProtection() && $this->_protectedTable) {
			$condition = $this->_getProtectedCondition();
			
			if (empty($where)) {
				$where = $condition;
			} else {
				if (!is_array($where)) {
					$where = array($where);
				}
				
				$where[] = $condition;
			}
		}
		
		return $where;
	}
		
	protected function _getProtectedCondition()
	{
		// Implementeras av subklass
		throw new Monday_Exception('_getProtectedCondition() must be implemented');
	}
	
	public static function setUseProtection($protected)
	{
		self::$_useProtection = $protected ? true : false;
	}
	
	public static function getUseProtection()
	{
		return self::$_useProtection;
	}
}