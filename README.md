# Update Crontab jobs for PHP

```php
use PNixx\Crontab\Crontab;
use PNixx\Crontab\Job;

//Initialize constructor
$crontab = new Crontab('example.com', '/path/to/example.com');

//Add job for run every minute
$job = new Job('bin/console hello');
$crontab->add($job);

//Add job for run hourly
$job = new Job('bin/console update');
$job
  ->setTime(Job::HOURLY)
  ->setLogFile('logs/execute.log');
$crontab->add($job);

//Add job for run custom time
$job = new Job('rm -Rf /var/cache');
$job->setTime('15 * * * *');
$crontab->add($job);

//Add job for run every two minutes
$job = new Job('echo "Hello World!"');
$job->setMinute('*/2');
$crontab->add($job);

//Update crontab
$crontab->update();

```

Result append block to your crontab:

	#===BEGIN Crontab for project: example.com
	* * * * * cd /path/to/example.com && bin/console hello
	
	0 * * * * cd /path/to/example.com && bin/console update >> /path/to/example.com/logs/execute.log
	
	15 * * * * cd /path/to/example.com && rm -Rf /var/cache
	
	*/2 * * * * cd /path/to/example.com && echo "Hello World!"
	
	#===END Crontab for project: example.com
