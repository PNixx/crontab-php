<?php
namespace PNixx\Crontab;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

function realpath($path) {
	return $path;
}

/**
 * @link https://pnixx.ru
 * @author Sergey Odintsov <nixx.dj@gmail.com>
 */
class CrontabTest extends TestCase {

	/**
	 * @var Crontab|MockObject
	 */
	protected $crontab;

	/**
	 * @var string
	 */
	protected $expected;

	//Setup variables
	public function setUp(): void {
		$job = new Job('test', '*/2 0');
		$job->setLogFile('test.log');

		$this->crontab = $this->getMockBuilder(Crontab::class)
			->onlyMethods(['read', 'save'])
			->setConstructorArgs(['phpunit', '/var'])->getMock();

		$this->crontab->add($job);

		// GIVEN
		$this->expected = '

#===BEGIN Crontab for project: phpunit
*/2 0 * * * cd /var && test >> /var/test.log

#===END Crontab for project: phpunit

';
	}

	//Generate with before blank crontab file
	public function testGenerateWithBlank() {

		// EXPECTS
		$this->crontab->expects($this->once())->method('read');
		$this->crontab->expects($this->once())->method('save');

		// WHEN
		$this->crontab->update();

		$this->assertEquals($this->expected, $this->getCrontabProperty()->getValue($this->crontab));
	}

	//Generate with updating crontab block
	public function testGenerateWithUpdating() {

		$current_crontab = <<<CRON
#===BEGIN Crontab for project: phpunit
*/2 0 * * * test

#===END Crontab for project: phpunit
CRON;

		// EXPECTS
		$this->crontab->expects($this->once())->method('read')->willReturnCallback(function() use ($current_crontab) {
			$this->getCrontabProperty()->setValue($this->crontab, $current_crontab);
		});
		$this->crontab->expects($this->once())->method('save');

		// WHEN
		$this->crontab->update();

		$this->assertEquals($this->expected, $this->getCrontabProperty()->getValue($this->crontab));
	}

	/**
	 * @return \ReflectionProperty
	 */
	protected function getCrontabProperty() {
		$reflector = new \ReflectionClass(Crontab::class);
		$property = $reflector->getProperty('crontab');
		$property->setAccessible(true);

		return $property;
	}
}
