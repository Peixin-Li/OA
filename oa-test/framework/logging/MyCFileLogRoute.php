<?php
/**
 * MyCFileLogRoute class file.
 *
 * @author qingwen ye 
 */

class MyCFileLogRoute extends CFileLogRoute
{
	// public function getLogPath()
	// {
	// 	return $this->_logPath;
	// }

	// /**
	//  * @param string $value directory for storing log files.
	//  * @throws CException if the path is invalid
	//  */
	// public function setLogPath($value)
	// {
	// 	$this->_logPath=realpath($value);
	// 	if($this->_logPath===false || !is_dir($this->_logPath) || !is_writable($this->_logPath))
	// 		throw new CException(Yii::t('yii','CFileLogRoute.logPath "{path}" does not point to a valid directory. Make sure the directory exists and is writable by the Web server process.',
	// 			array('{path}'=>$value)));
	// }

	/**
	 * Saves log messages in files.
	 * @param array $logs list of log messages
	 */
	protected function processLogs($logs)
	{
		$logFile=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
		if(@filesize($logFile)>$this->getMaxFileSize()*1024)
			$this->rotateFiles();
		$fp=@fopen($logFile,'a');
		@flock($fp,LOCK_EX);
		foreach($logs as $log)
			@fwrite($fp,$this->formatLogMessage($log[0],$log[1],$log[2],$log[3]));
		@flock($fp,LOCK_UN);
		@fclose($fp);
	}
}
