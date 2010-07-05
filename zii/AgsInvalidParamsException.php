<?php

class AgsInvalidParamsException extends CException
{
	public function __construct($where = '',$params = array(),$code = 0)
	{
		$paramInfoAr = array();
		if (is_array($params) && count($params)) 
		{
			foreach ($params as $key => $value) 
			{
				$paramInfoAr[] = $key.'=>'.(is_object($value)?get_class($value):$value);
			}
		}
		
		parent::__construct(Y::t('ags','err:invalidParams',array(
			'{where}'=>$where,
			'{paramInfo}'=>join(',',$paramInfoAr),
		)),$code);
	}
}