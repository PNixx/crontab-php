<?php
namespace PNixx\Crontab;

class Job {

	//Time constants
	const MINUTE = 0;
	const HOUR = 1;
	const DAY_OF_MONTH = 2;
	const MONTH = 3;
	const DAY_OF_WEEK = 4;

	//Template constants
	const YEARLY = '@yearly';
	const ANNUALLY = '@annually';
	const MONTHLY = '@monthly';
	const WEEKLY = '@weekly';
	const DAILY = '@daily';
	const HOURLY = '@hourly';

	/**
	 * @var array
	 */
	protected $time = [
		self::MINUTE       => '*',
		self::HOUR         => '*',
		self::DAY_OF_MONTH => '*',
		self::MONTH        => '*',
		self::DAY_OF_WEEK  => '*',
	];

	/**
	 * @var array
	 */
	protected $templates = [
		self::YEARLY   => '0 0 1 1 *',
		self::ANNUALLY => '0 0 1 1 *',
		self::MONTHLY  => '0 0 1 * *',
		self::WEEKLY   => '0 0 * * 0',
		self::DAILY    => '0 0 * * *',
		self::HOURLY   => '0 * * * *',
	];

	/**
	 * @var string
	 */
	protected $command;

	/**
	 * Working directory will be append command and log file
	 * @var string
	 */
	protected $path = null;

	/**
	 * @var string
	 */
	protected $log_file = null;

	/**
	 * @param string      $command Shell command
	 * @param string|null $time    Default time: * * * * *
	 */
	public function __construct($command, $time = null) {
		$this->command = $command;
		if( $time ) {
			$this->setTime($time);
		}
	}

	/**
	 * @param string $time Require full time string for unix crontab
	 * @return $this
	 * @throws Exception
	 */
	public function setTime($time) {

		//Set live template
		if( array_key_exists($time, $this->templates) ) {
			$this->time = explode(' ', $this->templates[$time]);
		} else {
			//Set custom time
			$time = explode(' ', $time);
			if( count($time) > 5 ) {
				throw new Exception('Incorrect time format. Max 5 values separate with space.');
			}
			$this->time = array_replace($this->time, $time);
		}

		return $this;
	}

	/**
	 * @param string $value
	 * @return $this
	 * @throws Exception
	 */
	public function setMinute($value) {
		$this->isTimeCorrect($value);
		$this->time[self::MINUTE] = $value;

		return $this;
	}

	/**
	 * @param $value
	 * @return $this
	 * @throws Exception
	 */
	public function setHour($value) {
		$this->isTimeCorrect($value);
		$this->time[self::HOUR] = $value;

		return $this;
	}

	/**
	 * @param $value
	 * @return $this
	 * @throws Exception
	 */
	public function setDayOfMonth($value) {
		$this->isTimeCorrect($value);
		$this->time[self::DAY_OF_MONTH] = $value;

		return $this;
	}

	/**
	 * @param $value
	 * @return $this
	 * @throws Exception
	 */
	public function setMonth($value) {
		$this->isTimeCorrect($value);
		$this->time[self::MONTH] = $value;

		return $this;
	}

	/**
	 * @param $value
	 * @return $this
	 * @throws Exception
	 */
	public function setDayOfWeek($value) {
		$this->isTimeCorrect($value);
		$this->time[self::DAY_OF_WEEK] = $value;

		return $this;
	}

	/**
	 * Set working directory will be append command and log file
	 * @param string $path
	 * @return $this
	 */
	public function setPath($path) {
		$this->path = $path;

		return $this;
	}

	/**
	 * If define path, we must set log file relative path
	 * @param string $file
	 * @return $this
	 */
	public function setLogFile($file) {
		$this->log_file = $file;

		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return implode(' ', $this->time) . ' ' . $this->getPath($this->command) . $this->getLogPath() . PHP_EOL;
	}

	/**
	 * @param $file
	 * @return string
	 */
	private function getPath($file) {
		if( $this->path ) {
			return 'cd ' . $this->path . ' && ' . $file;
		}

		return $file;
	}

	/**
	 * @param $file
	 * @return string
	 */
	private function getFullPath($file) {
		if( $this->path ) {
			return $this->path . '/' . $file;
		}

		return $file;
	}

	/**
	 * @return string
	 */
	private function getLogPath() {
		if( $this->log_file ) {
			return ' >> ' . $this->getFullPath($this->log_file);
		}

		return '';
	}

	/**
	 * @param $value
	 * @return bool
	 * @throws Exception
	 */
	private function isTimeCorrect($value) {
		if( strstr($value, ' ') ) {
			throw new Exception('Incorrect format: space not accept there');
		}

		return true;
	}
}