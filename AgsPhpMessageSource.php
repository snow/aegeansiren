<?php
/**
 * AgsPhpMessageSource class file.
 * customize message translating behavior
 *
 * @author Snow.Hellsing <snow.hellsing@gmail.com>
 */
class AgsPhpMessageSource extends CPhpMessageSource
{
	/**
	 * Override CMessageSource's version
	 * to make message be translate even when specified language is the same as source
	 * 重载这个类，就是为了使得即使应用设定的语言和用户选择的语言一致，仍查找语言文件
	 *
	 * @param string the message category
	 * @param string the message to be translated
	 * @param string the target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 * This parameter has been available since version 1.0.3.
	 * @return string the translated message (or the original message if translation is not needed)
	 */
	public function translate($category,$message,$language=null)
	{
		if($language===null)
			$language=Yii::app()->getLanguage();
		return $this->translateMessage($category,$message,$language);
	}
}