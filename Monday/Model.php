<?php 

class Monday_Model implements Iterator
{
	// Skilj på fält och värden för att kunna veta om ett värde inte är satt
	protected $_fields = array();
	protected $_values = array();
	
	// Bestämmer om values i modell är dynamiska eller ej
	protected $_canPopulate = true;
	
	// Bestämmer om modell kan mappas automatiskt
	protected $_canMap = true;
	
	// Mappade med key => värde, godtyckliga värden
	protected $_attributes = array();
	
	// Begränsar vilka attribut som får sättas
	protected $_allowedAttributes = array();

	public function __construct()
	{
		$this->_init();
	}
	
	protected function _init()
	{
		// Implementeras av subklass
	}

	/**
	 * Magisk för set/get
	 */
	public function __call($function, $args)
	{
		switch (true) {
			case preg_match('/^set/', $function):
				$field = self::functionToField($function);
	
				if (array_key_exists($field, $this->_fields)) {
					$this->_values[$field] = $args[0];					
				} else {
					throw new Monday_Exception("Invalid model field '$field'");
				}		
				break;
			case preg_match('/^get/', $function):
				$field = self::functionToField($function);
				
				if (isset($this->_fields['name_human'])) {
					var_dump($function); exit;
				}
				
				if (array_key_exists($field, $this->_fields)) {
					if (array_key_exists($field, $this->_values)) {
						return $this->_values[$field];
					} else {
						throw new Monday_Model_NotSetException("Field '$field' not set");
					}		

					return $this->_values[$field];
				} else {
					throw new Monday_Exception("Invalid model field '$field'");
				}		
			break;
			default:
				throw new Monday_Exception("Unavailable method '" . get_class($this) . "::$function'");
				break;
		}

		return $this;
	}
	
	/**
	 * Gör om t ex UserId till user_id
	 */
	public static function functionToField($value, $strip = true)
	{
		if ($strip) {
			$raw = preg_replace('/^[gs]et/', '', $value);
		} else {
			$raw = $value;
		}
		
		$raw = strtolower(substr($raw, 0, 1)) . substr($raw, 1);
		
		$field = '';
		
		for ($i = 0; $i < strlen($raw); $i++) {
			if (ctype_upper($raw{$i})) {
				$field .= '_';
			}
			
			$field .= strtolower($raw{$i});
		}
	
		return $field;
	}
	
	public static function fieldToFunction($value, $strip = true)
	{
		if ($strip) {
			$raw = preg_replace('/^[gs]et/', '', $value);
		} else {
			$raw = $value;
		}
	
		$function = '';
		
		for ($i = 0; $i < strlen($raw); $i++) {
			if ($raw{$i} == '_') {
				$i++;
				$function .= strtoupper($raw{$i});
			} else {
				$function .= $raw{$i};
			}
		}
		
		return ucfirst($function);
	}
	
	public function rewind()
	{
		reset($this->_values);
	}
	
	public function valid()
	{
		return (key($this->_values) !== null);
	}
	
	public function current()
	{
		// Använd getter för att ha stöd för overrides
		$getter = 'get' . self::fieldToFunction($this->key());
		
		return $this->$getter();
	}
	
	public function key()
	{
		return key($this->_values);
	}
	
	public function next()
	{
		next($this->_values);
	}

	public final function canPopulate()
	{
		return $this->_canPopulate;
	}
		
	public final function canMap()
	{
		return $this->_canMap;
	}
	
	public function addField($field)
	{
		$this->_fields[$field] = null;
		
		return $this;
	}
	
	public function setFields($fields)
	{
		if (!$this->canPopulate()) {
			throw new Exception('Cannot populate model automatically');
		}

		foreach ($fields as $field) {
			$this->addField($field);
		}
	}
	
	public function setValues($values)
	{
		$errors = array();
	
		// Använd setters för att inkludera egna setters
		foreach ($values as $key => $val) {
			try {
				$setter = 'set' . self::fieldToFunction($key);
				$this->$setter($val);
			} catch (Monday_Model_SetException $e) {
				$errors[$key] = $e->getMessage();
			}
		}
		
		if (!empty($errors)) {
			$e = new Monday_Exception('Errors when setting model values');
			$e->setErrorMessages($errors);
			throw $e;
		}
		
		return $this;
	}
		
	public function getAttributes()
	{
		return $this->_attributes;
	}
	
	public function getAttribute($name)
	{
		if (array_key_exists($name, $this->_attributes)) {
			return $this->_attributes[$name];
		}
		
		// Attributes mår bättre av att vara null om inte satta
		#throw new Maklarsys_Exception("No such attribute '$name'");
		return null;	
	}
	
	public function setAttributes($attrs)
	{
		$errors = array();	
	
		foreach ($attrs as $key => $val) {
			try {
				$setter = 'setAttribute' . self::fieldToFunction($key);
				
				if (method_exists($this, $setter)) {
					$this->$setter($val);
				} else {
					$this->setAttribute($key, $val);
				}
			} catch (Monday_Model_SetException $e) {
				$errors[$key] = $e->getMessage();
			}
		}
		
		if (!empty($errors)) {
			$e = new Monday_Exception('Errors when setting model attributes');
			$e->setErrorMessages($errors);
			throw $e;
		}
		
		return $this;
	}
	
	public function setAttribute($name, $value)
	{
		if (!in_array($name, $this->_allowedAttributes)) {
			throw new Monday_Model_SetException("Attribute '$name' isn't allowed for " . get_class($this));		
		}

		$value = (string)$value;
		$this->_attributes[$name] = $value;
		
		return $this;
		#throw new Maklarsys_Exception("Attribute value must be a string");
	}

	/**
	 * Populera modell med värden från t ex tabell
	 */
	public function populate($values)
	{
		$this->setFields(array_keys($values));
		$this->setValues($values);
		
		return $this;
	}
}