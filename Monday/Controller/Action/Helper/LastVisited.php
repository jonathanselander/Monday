<?php

class Monday_Controller_Action_Helper_LastVisited
	extends Zend_Controller_Action_Helper_Abstract
{
	public function postDispatch()
	{
		if ($this->getRequest()->getControllerName() !== 'error') {
			$sn = new Zend_Session_Namespace('history');
			$sn->lastVisited = $this->getRequest()->getRequestUri();
		}
	}

	public function direct()
	{
		$sn = new Zend_Session_Namespace('history');
		$current = $this->getRequest()->getRequestUri();

		// Prevent infinite redirects
		return $current == $sn->lastVisited ? '/' : $sn->lastVisited;
	}
}