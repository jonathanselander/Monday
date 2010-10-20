<?php

class Monday_Directory_Walker_Callback_Symlink
	extends Monday_Directory_Walker_Callback_Abstract
	implements Monday_Directory_Walker_Callback_Interface
{
	private $_target;
	private $_strip = '';

	public function setStrip($strip)
	{
		$this->_strip = str_replace('#', '\#', $strip);
		return $this;
	}

	public function setTarget($target)
	{
		$this->_target = $target;
		return $this;
	}

	public function getStrip()
	{
		return $this->_strip;
	}

	public function getTarget()
	{
		return $this->_target;
	}

	private function strip($path)
	{
		return preg_replace('#' . $this->_strip . '#', '', $path);
	}

	public function call($file, $workingDir)
	{
		if (!$file)
			return;

		$target = "$workingDir/$file";
		$link = $this->_target . '/' . $this->strip($workingDir) . "/$file";

		// create direectory, only symlink file
		if ($file instanceof Monday_Directory) {
			$old_umask = umask(0);
			mkdir($link, 01777); # sticky bit
			umask($old_umask);
		} else
			symlink($target, $link);
	}
}
