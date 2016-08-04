<?php
namespace PNixx\Crontab;

/**
 * @link https://pnixx.ru
 * @author Sergey Odintsov <nixx.dj@gmail.com>
 */
class JobTest extends \PHPUnit_Framework_TestCase {

	public function testEveryMinutes() {
		$job = new Job('test');

		$this->assertEquals('* * * * * test' . PHP_EOL, $job->__toString());
	}

	public function testTimeAndLog() {
		$job = new Job('test');
		$job
			->setTime(Job::WEEKLY)
			->setLogFile('test.log');

		$this->assertEquals('0 0 * * 0 test >> test.log' . PHP_EOL, $job->__toString());
	}

	public function testPath() {
		$job = new Job('test', Job::MONTHLY);
		$job
			->setPath('/var')
			->setLogFile('test.log');

		$this->assertEquals('0 0 1 * * cd /var && test >> /var/test.log' . PHP_EOL, $job->__toString());
	}

	public function testEveryTwoMinutes() {
		$job = new Job('test', '*/2');

		$this->assertEquals('*/2 * * * * test' . PHP_EOL, $job->__toString());
	}

	public function testSetCustomTime() {
		$job = new Job('test');
		$job
			->setPath('/path/to/project')
			->setMinute(5)
			->setHour(0)
			->setMonth(1)
			->setDayOfMonth(11)
			->setDayOfWeek(4);

		$this->assertEquals('5 0 11 1 4 cd /path/to/project && test' . PHP_EOL, $job->__toString());
	}

	public function testRebootTrigger() {
		$job = new Job('test', Job::REBOOT);

		$this->assertEquals('@reboot test' . PHP_EOL, $job->__toString());
	}
}