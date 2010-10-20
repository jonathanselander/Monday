<?php

require_once 'Monday/Directory/Walker/Callback/Abstract.php';
require_once 'Monday/Directory/Walker/Callback/Interface.php';

class Monday_Directory_Walker_Callback_Clone
	extends Monday_Directory_Walker_Callback_Abstract
	implements Monday_Directory_Walker_Callback_Interface
{
	private $_target = null;

	public function setTarget($target)
	{
		$this->_target = $target;
	}

	public function getTarget()
	{
		return $this->_target;
	}

	public function call($file, $workingDir)
	{
		if (!$file)
			return;

		$target = "$workingDir/$file";

		if (!($file instanceof Monday_Directory) && is_link($target) === false)
			unlink($target);
	}
}
