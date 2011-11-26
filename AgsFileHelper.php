<?php
/**
 * @deprecated will be rewrite soon/此类命不久矣
 *
 * TODO:
 * use prefix instead of dir
 * merge this class and AgsUploadHelper
 *
 * @author snow@firebloom.cc
 *
 */
class AgsFileHelper extends AgsUploadHelper
{
	/**
	 * save a uri pointed resource on server
	 * 把一个uri指向的资源保存在服务器上
	 *
	 * @param string url to save/要保存的资源的url
	 * @param string url referrer if needed/如果需要设置referrer
	 * @param string dir to save file {@link saveFileObject}/保存到哪个文件夹
	 */
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

		// TODO rewrite to $this->saveFileObject
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