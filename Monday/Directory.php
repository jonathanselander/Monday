<?php

class Monday_Directory implements Iterator, Countable
{
	private $_dir;
	private $_workingDir = '';
	private $_dirName = '';
	private $_tmpFile = null;
	private $_ignoreFiles = array();

	public function __construct($dir = '', $ignore = array(), $workingDir = '')
	{
		if ($dir instanceof Monday_Directory)
			$dir = $dir->__toString();

		if ($workingDir instanceof Monday_Directory)
			$workingDir = $workingDir->__toString();

		if (is_string($dir) && !empty($dir))
			$this->_openDir($dir, $workingDir);

		if (is_array($ignore))
			$this->setIgnore($ignore);
	}

	public function __destruct()
	{
		$this->_closeDir();
	}

	public function __toString()
	{
		return $this->_dirName;
	}

	public function count()
	{
		$count = 0;

		while (($file = readdir($this->_dir)) !== false)
			$count++;

		$this->rewind();
		return $count;
	}

	public function isEmpty()
	{
		$count = 0;

		while (($file = readdir($this->_dir)) !== false) {
			$count++;
			if ($count > 2) {
				$this->rewind();
				return false;
			}
		}

		$this->rewind();
		return true;
	}

	public function setIgnore($ignore)
	{
		if (!is_array($ignore))
			throw new Monday_Directory_Exception('Ignored files have to be an array');

		$this->_ignoreFiles = $ignore;
	}

	public function getIgnore()
	{
		return $this->_ignoreFiles;
	}

	public function getCwd()
	{
		return $this->_workingDir;
	}

	private function _openDir($dir, $workingDir)
	{
		if (!empty($workingDir))
			$path = "$workingDir/$dir";
		else
			$path = $dir;

		$this->_workingDir = $path;

		if (($this->_dir = @opendir($path)) === false) {
			require_once 'Monday/Directory/Exception.php';
			throw new Monday_Directory_Exception('Couldn\'t read directory "' . $path . '"');
		}

		$this->_dirName = $dir;
	}

	private function _closeDir()
	{
		if (is_resource($this->_dir))
			closedir($this->_dir);
	}

	public function rewind()
	{
		if (is_resource($this->_dir))
			rewinddir($this->_dir);
	}

	public function current()
	{
		return (!($this->_tmpFile) ? $this->next() : $this->_tmpFile);
	}

	public function key() 
	{
		return 0;
	}

	public function next()
	{
		$this->_tmpFile = readdir($this->_dir);

		if (in_array($this->_tmpFile, $this->_ignoreFiles))
			$this->_tmpFile = $this->next();

		if ($this->_tmpFile == $this->_dirName)
			$this->_tmpFile = $this->next();

		if (	$this->_tmpFile !== false &&
					is_dir($this->_workingDir . '/' . $this->_tmpFile) &&
					($this->_tmpFile != '.' && $this->_tmpFile != '..')) {
			$this->_tmpFile = new Monday_Directory($this->_tmpFile, $this->_ignoreFiles, $this->_workingDir);
		}

		return $this->_tmpFile;
	}

	public function valid()
	{
		if (!is_resource($this->_dir))
			return false;
		elseif ($this->_tmpFile === false)
			return false;

		return true;
	}

	public function wipe()
	{
		if (!is_resource($this->_dir))
			return false;

		$this->_wipeReal($this, $this->_workingDir);

		return true;
	}

	private function _wipeReal($dir, $path)
	{
		foreach ($dir as $file) {
			if ($file == '.' || $file == '..')
				continue;

			$file_path = "$path/$file";

			if ($file instanceof Monday_Directory)
				$this->_wipeReal($file, $file_path);
			else {
				if (@unlink($file_path) === false) {
					require_once 'Monday/Directory/Exception.php';
					throw new Monday_Directory_Exception('Kunde inte ta bort fil "' . $file . '"');
				}
			}
		}

		if (@rmdir($path) === false) {
			require_once 'Monday/Directory/Exception.php';
			throw new Monday_Directory_Exception('Kunde inte ta bort katalog "' . $file . '"');
		}
	}
}
