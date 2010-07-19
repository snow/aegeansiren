<?php

class AgsFileHelper extends AgsUploadHelper
{
	public function saveFileFromUrl($url,$referrer = '',$dir = 'misc')
	{
		if (!$url)
		{
			throw new CException('err:notNull:url');
		}

		$context = stream_context_create(array(
			'http'=>array(
				'method'=>'GET',
				'header'=>'Referer: '.$referrer."\r\n"
					.'User-Agent: '.Y::r()->userAgent,
			),
		));

		$fileObj = file_get_contents($url,false,$context);

		if (!$fileObj)
			throw new CException('err:failedToGetRemoteFile:'.$url);

		$fileName = Y::a()->uploadHelper->saveFileObject($fileObj,$url,$dir);

		if (!$fileName)
			throw new CException('err:failedToSaveFile:'.$url);

		return $fileName;
	}

	public function getFilepathFromName($filename,$dir)
	{
		return $this->dirBase.'/'.$dir.'/'.$filename;
	}
}