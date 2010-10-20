<?php

class Monday_Directory_Walker
{
	private $_root = null;
	private $_callback = null;

	public function __construct($dir = null, $ignore = array(), $callback = null)
	{
		if (!is_null($dir))
			$this->setDir($dir, $ignore);
		if (!is_null($callback))
			$this->setCallback($callback);
	}

	public function setDir($dir, $ignore = array()) 
	{
		if ($dir instanceof Monday_Directory) {
			$this->_root = $dir;
		} else {
			require_once 'Monday/Directory.php';
			$this->_root = new Monday_Directory($dir, $ignore);
		}
	}

	public function setCallback($callback)
	{
		if ($callback instanceof Monday_Directory_Walker_Callback_Abstract)
			$this->_callback = $callback;
		elseif (function_exists($callback) === true)
			$this->_callback = $callback;
		else {
			require_once 'Monday/Directory/Exception.php';
			throw new Monday_Directory_Exception('No such callback class or function');
		}
	}

	public function walk()
	{
		if (!($this->_root instanceof Monday_Directory)) {
			require_once 'Monday/Directory/Exception.php';
			throw new Monday_Directory_Exception('Illegal directory "' . $this->_root . '"');
		}

		return $this->_walker($this->_root);
	}

	private function _walker($dir)
	{
		$files = array();

		foreach ($dir as $file) {
			if (!is_null($this->_callback)) {
				if ($this->_callback instanceof Monday_Directory_Walker_Callback_Abstract)
					$this->_callback->call($file, $dir->getCwd());
				else
					call_user_func($this->_callback, $file, $dir->getCwd());
			}

			if ($file instanceof Monday_Directory) {
				$files[] = $dir->getCwd() . '/' . $file->__toString();
				$files[] = $this->_walker(clone $file, $dir->getIgnore(), $dir->getCwd());
			} else
				$files[] = $file;

		}

		return $files;
	}
}
