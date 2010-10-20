<?php

class Monday_Directory_Walker_Callback_Cleaner
	extends Monday_Directory_Walker_Callback_Abstract
	implements Monday_Directory_Walker_Callback_Interface
{
	public function call($file, $workingDir)
	{
		if (!$file)
			return;

		$target = "$workingDir/$file";

		if (!($file instanceof Monday_Directory) && is_link($target) === false)
			unlink($target);
	}
}
