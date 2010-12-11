<?php

class Monday_Filter_File_ImageResize implements Zend_Filter_Interface
{
	const DEFAULT_QUALITY = 100;
	private $_options = array();

	public function __construct($options)
	{
		if ($options instanceof Zend_Config) {
			$options = $options->toArray();
		} elseif (is_string($options)) {
			$options = array('target' => $options);
		} elseif (!is_array($options)) {
			require_once 'Zend/Filter/Exception.php';
			throw new Zend_Filter_Exception('Invalid options argument provided to filter');
		}

		$this->_options = $options;
	}

	public function filter($value)
	{
		// get image file format
		$ext = preg_replace('/.*\./', '', $value);

		// create new image from source file
		$imgSrc = null;

		// get present height / width
		$imgSrcInfo = getimagesize($value);
		$imgSrcWidth = $imgSrcInfo[0];
		$imgSrcHeight = $imgSrcInfo[1];

		$w = !empty($this->_options['width']) ? $this->_options['width'] : $imgSrcWidth;
		$h = !empty($this->_options['height']) ? $this->_options['height'] : $imgSrcHeight;

		// calculate proportions
		if (isset($this->_options['keepProportions']) &&
			$this->_options['keepProportions'] === true) {
			if ($imgSrcWidth > $imgSrcHeight) {
				$h = round($imgSrcHeight*($w/$imgSrcWidth));
			} else {
				$w = round($imgSrcWidth*($h/$imgSrcHeight));
			}
		}

		switch ($ext) {
			case 'jpg' :
				$imgSrc = imagecreatefromjpeg($value);
				break;
			case 'jpeg' :
				$imgSrc = imagecreatefromjpeg($value);
				break;
			case 'gif' :
				$imgSrc = imagecreatefromgif($value);
				break;
			case 'png' :
				$imgSrc = imagecreatefrompng($value);
				break;
			default :
				throw new Zend_Filter_Exception("Unable to resize image, invalid file format");
		}

		// make sure that the new file was created
		if (is_null($imgSrc)) {
			throw new Zend_Filter_Exception("An unknown error occurred while reszing the image");
		}

		// create a new true color image
		$imgDest = imagecreatetruecolor($w, $h);

		if (!imagecopyresampled($imgDest, $imgSrc, 0, 0, 0, 0, $w, $h, $imgSrcWidth, $imgSrcHeight)) {
			throw new Zend_Filter_Exception("Unable to resample image");
		}

		// destroy source handle
		imagedestroy($imgSrc);

		// create source image handle
		if (!empty($forceExtension)) {
			$ext = $forceExtension;
		}

		$tmpFile = tempnam('/tmp', 'imageresize');

		$quality = !empty($this->_options['quality']) ? $this->_options['quality'] :
			self::DEFAULT_QUALITY;

		// actually create the image
		switch ($ext) {
			case 'jpg' :
				imagejpeg($imgDest, $tmpFile, $quality);
				break;
			case 'jpeg' :
				imagejpeg($imgDest, $tmpFile, $quality);
				break;
			case 'gif' :
				imagegif($imgDest, $tmpFile);
				break;
			case 'png' :
				imagepng($imgDest, $tmpFile);
				break;
			default :
				throw new Zend_Filter_Exception("Unable to resize image, invalid file format");
		}

		rename($tmpFile, $value);
		imagedestroy($imgDest);

		return $value;
	}
}
