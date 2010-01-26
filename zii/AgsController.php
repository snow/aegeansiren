<?php

class AgsController extends CController
{
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

	public function render($fragments=array(),$params=array(),$return=false)
	{
		$viewData = array();

		if (!isset($params['layout']))
		{
			$params['layout'] = 'm0';
		}

		if (!isset($params['docClass']))
		{
			$params['docClass'] = 'w950';
		}

		if (!isset($fragments['header']))
		{
			$fragments['header'] = array('view'=>'/system/header');
		}

		if (!isset($fragments['footer']))
		{
			$fragments['footer'] = array('view'=>'/system/footer');
		}

		foreach ($fragments as $fgmName=>$fgmData)
		{
			if (is_array($fgmData) && isset($fgmData['view']))
			{
				$view = $fgmData['view'];
				unset($fgmData['view']);
				$viewData[$fgmName] = $this->renderPartial($view,$fgmData,true);
			}
			elseif (is_string($fgmData))
			{
				$viewData[$fgmName] = $fgmData;
			}
			else
			{
				$viewData[$fgmName] = '';
				if ('header' === $fgmName)
				{
					$params['docClass'] .= ' noHd';
				}
				if ('footer' === $fgmName)
				{
					$params['docClass'] .= ' noFt';
				}
			}
		}

		$viewData = array_merge($viewData,$params);
		$output=$this->renderFile($this->getLayoutFile('main'),$viewData,true);

		$output=$this->processOutput($output);

		if($return)
			return $output;
		else
			echo $output;
	}

	public function setPageTitle($title)
	{
		if ($title)
		{
			$title .= ' - ';
		}
		$this->pageTitle = $title.Y::t('ags','sitename');
	}
}