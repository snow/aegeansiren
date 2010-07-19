<?php

class AgsUploadHelper extends CComponent
{
	public $urlBase;
	public $dirBase;
	public static $allowdFileTypes = array(
		'image'=>array(
			'image/jpeg' => 'jpeg',
			'image/pjpeg' => 'jpeg',
			'image/png' => 'png',
			'image/x-png' => 'png',
			'image/gif' => 'gif'
		),
		'flash'=>array(
			'application/x-shockwave-flash'=>'swf',
		),
	);

	public function init()
	{
		if (null === $this->dirBase)
		{
			$this->dirBase = Yii::getPathOfAlias('webroot.upload');
		}

		if (null === $this->urlBase)
		{
			$this->urlBase = '/upload';
		}
	}

	public function saveUploadedFile($file,$type,$dir='misc')
	{
		if (!(UPLOAD_ERR_OK === $file['error'] && 0 < $file['size']))
			throw new CException('err:uploadFailed:'.$file['name']);

		if (!key_exists($file['type'],self::$allowdFileTypes[$type]))
			throw new CException('err:uploadFileTypeNotAllowed:'.$file['type']);

		$filename = $this->genFilename($file['name'],$dir);
		$filePath = $this->dirBase.'/'.$dir.'/'.$filename;
		if (!move_uploaded_file($file['tmp_name'],$filePath))
			throw new CException('err:moveUploadedFileFailed:'.$file['name'].','.$filePath);

		return $filename;
	}

	public function saveFileObject($content,$filename,$dir)
	{
		if (!($content && $filename && $dir))
			throw new CException('err:notNull');

		$filename = $this->genFilename($filename,$dir);

		if (!file_put_contents($this->dirBase.'/'.$dir.'/'.$filename,$content))
			throw new CException('err:saveFileFailed:'.$filename);

		return $filename;
	}

	public function getPathFromLocalUrl($url)
	{
		return strtr($url,array($this->urlBase=>$this->dirBase));
	}

	public function getUrlFromPath($path)
	{
		return strtr($path,array($this->dirBase=>$this->urlBase));
	}

	public function genFilename($originName,$dir)
	{
		$path = date('Ym',time()).'/'.date('d',time());
		if (!(is_dir($this->dirBase.'/'.$dir.'/'.$path) || mkdir($this->dirBase.'/'.$dir.'/'.$path,0777,true)))
			throw new CException('err:failedToMkdir:'.$this->dirBase.'/'.$dir.'/'.$path);

		$ext = array_pop(explode('.',array_pop(explode('/',array_shift(explode('?',strtr(trim($originName),array('\\'=>'/')))))),2));
		$filename = $path.'/'.S::getRandomString().'.'.$ext;
		while (file_exists($this->dirBase.'/'.$dir.'/'.$filename))
		{
			$filename = $path.'/'.S::getRandomString().'.'.$ext;
		}
		return $filename;
	}

	public function genFilenameWithoutExt($dir)
	{
		$path = date('Ym',time()).'/'.date('d',time());
		if (!(is_dir($this->dirBase.'/'.$dir.'/'.$path) || mkdir($this->dirBase.'/'.$dir.'/'.$path,0777,true)))
			throw new CException('err:failedToMkdir:'.$this->dirBase.'/'.$dir.'/'.$path);

		$filename = $path.'/'.S::getRandomString();
		while (file_exists($this->dirBase.'/'.$dir.'/'.$filename))
		{
			$filename = $path.'/'.S::getRandomString();
		}
		return $filename;
	}

	public function delFile($fileName,$dir)
	{
		unlink($this->dirBase.'/'.$dir.'/'.$fileName);
	}

	public function getResizedImageFromFile($imageFile, $maxwidth, $maxheight, $params=array())
	{
		$params = array_merge(array(
			'square'=>false,
			'x1'=>0,
			'y1'=>0,
			'x2'=>0,
			'y2'=>0,
			'upscale'=>false,
		),$params);
		// Get the size information from the image
		$imgsizearray = getimagesize($imageFile);
		if ($imgsizearray == FALSE) {
			return FALSE;
		}

		// Get width and height
		$width = $imgsizearray[0];
		$height = $imgsizearray[1];

		// make sure we can read the image
		$accepted_formats = array(
			'image/jpeg' => 'jpeg',
			'image/pjpeg' => 'jpeg',
			'image/png' => 'png',
			'image/x-png' => 'png',
			'image/gif' => 'gif'
		);

		// make sure the function is available
		$load_function = "imagecreatefrom" . $accepted_formats[$imgsizearray['mime']];
		if (!is_callable($load_function)) {
			return FALSE;
		}

		// crop image first?
		$crop = TRUE;
		if ($params['x1'] == 0 && $params['y1'] == 0 && $params['x2'] == 0 && $params['y2'] == 0) {
			$crop = FALSE;
		}

		// how large a section of the image has been selected
		if ($crop) {
			$region_width = $params['x2'] - $params['x1'];
			$region_height = $params['y2'] - $params['y1'];
		} else {
			// everything selected if no crop parameters
			$region_width = $width;
			$region_height = $height;
		}

		// determine cropping offsets
		if ($params['square']) {
			// asking for a square image back

			// detect case where someone is passing crop parameters that are not for a square
			if ($crop == TRUE && $region_width != $region_height) {
				return FALSE;
			}

			// size of the new square image
			$new_width = $new_height = min($maxwidth, $maxheight);

			// find largest square that fits within the selected region
			$region_width = $region_height = min($region_width, $region_height);

			// set offsets for crop
			if ($crop) {
				$widthoffset = $params['x1'];
				$heightoffset = $params['y1'];
				$width = $params['x2'] - $params['x1'];
				$height = $width;
			} else {
				// place square region in the center
				$widthoffset = floor(($width - $region_width) / 2);
				$heightoffset = floor(($height - $region_height) / 2);
			}
		} else {
			// non-square new image

			$new_width = $maxwidth;
			$new_height = $maxheight;

			// maintain aspect ratio of original image/crop
			if (($region_height / (float)$new_height) > ($region_width / (float)$new_width)) {
				$new_width = floor($new_height * $region_width / (float)$region_height);
			} else {
				$new_height = floor($new_width * $region_height / (float)$region_width);
			}

			// by default, use entire image
			$widthoffset = 0;
			$heightoffset = 0;

			if ($crop) {
				$widthoffset = $params['x1'];
				$heightoffset = $params['y1'];
			}
		}

		// check for upscaling
		// @todo This ignores squares, coordinates, and cropping. It's probably not the best idea.
		// Size checking should be done in action code, but for backward compatibility
		// this duplicates the previous behavior.
		if (!$params['upscale'] && ($height < $new_height || $width < $new_width)) {
			// zero out offsets
			$widthoffset = $heightoffset = 0;

			// determine if we can scale it down at all
			// (ie, if only one dimension is too small)
			// if not, just use original size.
			if ($height < $new_height && $width < $new_width) {
				$ratio = 1;
			} elseif ($height < $new_height) {
				$ratio = $new_width / $width;
			} elseif ($width < $new_width) {
				$ratio = $new_height / $height;
			}
			$region_height = $height;
			$region_width = $width;
			$new_height = floor($height * $ratio);
			$new_width = floor($width * $ratio);
		}

		// load original image
		$orig_image = $load_function($imageFile);
		if (!$orig_image) {
			return FALSE;
		}

		// allocate the new image
		$newimage = imagecreatetruecolor($new_width, $new_height);
		if (!$newimage) {
			return FALSE;
		}

		// create the new image
		$rtn_code = imagecopyresampled(	$newimage,
										$orig_image,
										0,
										0,
										$widthoffset,
										$heightoffset,
										$new_width,
										$new_height,
										$region_width,
										$region_height );
		if (!$rtn_code) {
			return FALSE;
		}

		// grab contents for return
		ob_start();
		imagejpeg($newimage, null, 90);
		$jpeg = ob_get_clean();

		imagedestroy($newimage);
		imagedestroy($orig_image);

		return $jpeg;
	}
}