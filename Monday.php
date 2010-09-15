<?php

/**
 * Generell klass för att hämta modeller mappade till tabeller
 * utan att behöva separata modellfiler
 */
class Monday
{
	private static $_namespace = 'Monday';
	
	// Mappad med 'klassnamn' => $objekt
	private static $_singletonCache = array();

	public static function setNamespace($ns)
	{
		self::$_namespace = $ns;
	}

	public static function getNamespace()
	{
		return self::$_namespace;
	}
	
	public static function load($class, $namespace = null)
	{
		if (empty($namespace)) {
			$namespace = self::getNamespace(); 
		}

		$class = ucfirst($class);
		$className = $namespace . "_$class";

		$path = str_replace('_', '/', $className);
		
		@include_once "$path.php";
		
		if (!@class_exists($className)) {
			throw new Exception('Incorrect class');
		}

		return new $className;
	}
	
	public static function model($class, $namespace = null)
	{
		// Skapa modell
		try {
			$className = "Model_" . Monday_Model::fieldToFunction($class);
			$model = self::load($className, $namespace);
		} catch (Exception $e) {
			// Det finns ingen färdig klass, skapa ny med generella medel
			$model = new Monday_Model;
		}
				
		return $model;
	}
	
	public static function table($class, $namespace = null)
	{
		try {
			$className = "Db_Table_$class";
			$table = self::load($className, $namespace);
		} catch (Exception $e) {
			$tableName = strtolower($class);
			$tableClass = self::getNamespace() . '_Db_Table';
			$table = new $tableClass(array(
				Zend_Db_Table_Abstract::NAME => $tableName,
				Zend_Db_Table_Abstract::PRIMARY => 'id'
			));
		}
				
		return $table;
	}

	public static function mapper($class, $namespace = null)
	{
		$class = ucfirst($class);
	
		$model = self::model($class, $namespace);
		
		if (!$model->canMap()) {
			throw new Exception("Model '$class' doesn't allow mapping");
		}
		
		// Skapa mapper
		try {
			$className = "Model_Mapper_" . Monday_Model::fieldToFunction($class);
			
			#echo "$className<br>";
			$mapper = self::load($className, $namespace);
		} catch (Exception $e) {
			$mapper = new Monday_Model_Mapper;
		}
		
		$mapper->setModelName(get_class($model));
		
		// Populera med tabelldata om modell kan populeras
		if ($model->canPopulate()) {
			$table = self::table($class, $namespace);
			
			if ($table instanceof Monday_Db_Table) {
				$mapper->setTable($table);
			}
		}
		
		return $mapper;
	}
	
	public static function mapperSingleton($class, $namespace = null)
	{
		if (!isset(self::$_singletonCache[$class])) {
			self::$_singletonCache[$class] = self::mapper($class, $namespace);
		}
		
		return self::$_singletonCache[$class];
	}
}