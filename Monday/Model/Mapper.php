<?php

class Monday_Model_Mapper
{
	protected $_modelName;
	protected $_table;
	
	// TODO: Hårdkodade fält som *måste* vara satta vid save()
	protected $_fieldsRequired = array();
	
	public final function __construct()
	{
		$this->_init();
	}
	
	public function __call($function, $args)
	{
		switch (true) {
			case preg_match('/^fetchBy[A-Z]/', $function):
				$field = preg_replace('/^fetchBy/', '', $function);
				$field = Monday_Model::functionToField($field);
				return $this->_fetchSpecificField($field, $args);
				break;
		}
		
		throw new Exception("Incorrect function call '$function'", 400);
	}
		
	protected function _init()
	{
		// Implementeras av subklass
	}

	protected function _fetchSpecificField($field, $args)
	{
		$rows = $this->getTable()
			->fetchSpecificField($field, $args);

		$models = array();
		
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$primary = $this->getTable()->getPrimary();
				$primary = array_pop($primary);
				$id = $row[$primary];
				$models[] = $this->load($id);
			}
		}
				
		return $models;
	}	
	
	public function setTable($table)
	{
		if ($table instanceof Monday_Db_Table) {
			$this->_table = $table;
		} else {
			$this->_table = new Monday_Db_Table($table);
		}
		
		return $this;
	}
	
	public function getTable()
	{
		return $this->_table;
	}
	
	public function setModelName($model)
	{
		$this->_modelName = $model;
		
		return $this;
	}
	
	public function getModelName()
	{
		return $this->_modelName;
	}
	
	protected function _beforeCreate()
	{
		// Implementeras av subklass
	}
	
	protected function _afterCreate(Monday_model $model)
	{
		// Implementeras av subklass
	}
	
	// Skapa ny instans av modell
	public function create()
	{
		$this->_beforeCreate();
	
		$className = $this->getModelName();
		$model = new $className;

		if ($model->canPopulate()) {
			$fields = array_keys($this->getTable()->getFields());
			$model->setFields($fields);
		}
		
		$this->_afterCreate($model);

		return $model;
	}
	
	protected function _beforeLoad(Monday_Model $model)
	{
		// Implementeras av subklass
	}
	
	protected function _afterload(Monday_Model $model)
	{
		// Implementeras av subklass
	}
	
	protected function _loadAttributes(Monday_Model $model)
	{
		// Implementeras av subklass
	}	
	
	protected function _saveAttributes(Monday_Model $model)
	{
		// Implementeras av subklass
	}
	
	public function load($id)
	{
		$model = $this->create();
		$this->_beforeLoad($model);

		$table = $this->getTable();
		$row = $table->find($id)
			->current();
			
		if (!$row) {
			$info = $table->info();
			throw new Exception("No row with id '$id' in table '" . $info['name'] . "'", 404);
		}
		
		$values = $row->toArray();

		$model->populate($values);
		$this->_loadAttributes($model);
		$this->_afterLoad($model);
			
		return $model;
	}
	
	protected function _beforeSave(Monday_Model $model)
	{
		// Implementeras av subklass
	}
	
	protected function _afterSave(Monday_Model $model)
	{
		// Implementeras av subklass
	}
	
	public function save(Monday_Model $model)
	{
		$this->_beforeSave($model);
	
		$table = $this->getTable();
		
		$info = $table->info();
		$primaryGetter = 'get' . Monday_Model::fieldToFunction(reset($info['primary']), false);

		try {
			$row = $table->find($model->$primaryGetter())
				->current();
		} catch (Exception $e) {}

		if (isset($row) && $row) {
			// Uppdatera rad
			foreach ($model as $key => $value) {
				$row->$key = $value;
			}
			
			$row->save();
		} else {
			// Skapa ny
			$values = array();
			
			foreach ($model as $key => $value) {
				if ($key == $info['primary']) {
					continue;
				}
				
				$values[$key] = $value;
			}
			
			$id = $table->insert($values);
			$primarySetter = 'set' . Monday_Model::fieldToFunction(reset($info['primary']), false);
			$model->$primarySetter($id);
		}
		
		$this->_saveAttributes($model);
		$this->_afterSave($model);

		return $this;
	}
	
	public function delete(Monday_Model $model)
	{
		$table = $this->getTable();
		
		$info = $table->info();
		$primaryGetter = 'get' . Monday_Model::fieldToFunction(array_pop($info['primary']));
		
		$row = $table->find($model->$primaryGetter())
			->current();
			
		if ($row) {
			$row->delete();
		}
	}
	
	public function fetchAll($where = null, $order = null, $limit = null)
	{
		$rows = $this->getTable()
			->fetchAll($where, $order, $limit);
		
		$info = $this->getTable()->info();
		$primary = $info['primary'];
		
		if (count($primary) == 1) {
			$primary = array_pop($primary);
		}
		
		$models = array();

		foreach ($rows as $row) {
			$models[] = $this->load($row->$primary);
		}
		
		return $models;
	}
}