# Crontab PHP

Crontab for PHP provides a clear syntax for writing and deploying cron jobs (inspired by [whenever](https://github.com/javan/whenever)).

### Installation

```sh
$ composer require pnixx/crontab
```

### Usage

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

Result append or replace block to your crontab:

	#===BEGIN Crontab for project: example.com
	* * * * * cd /path/to/example.com && bin/console hello
	
	0 * * * * cd /path/to/example.com && bin/console update >> /path/to/example.com/logs/execute.log
	
	15 * * * * cd /path/to/example.com && rm -Rf /var/cache
	
	*/2 * * * * cd /path/to/example.com && echo "Hello World!"
	
	#===END Crontab for project: example.com

### Capistrano\Symfony integration

See on [Capistrano::Symfony documentation](https://github.com/capistrano/symfony) plugin for reference. 

Symfony 3 command class for generation crontab

```php
use PNixx\Crontab\Crontab;
use PNixx\Crontab\Job;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrontabUpdateCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this->setName('crontab:update');
        $this->setDescription('Update all cron tasks for project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root_path = realpath($this->getContainer()->get('kernel')->getRootDir() . '/..');

        //Initialize constructor crontab for current environment
        $crontab = new Crontab($this->getContainer()->get('kernel')->getEnvironment(), $root_path);

        //Add your jobs
        $crontab->add(new Job('echo "Hello World!"'));

        //Update
        $crontab->update();
    }
}
```

Add the following to `deploy.rb` for Capistrano '~> 3.5'

```ruby
namespace :deploy do
  task :crontab do
    on roles(:db) do
      invoke 'symfony:console', 'crontab:update', '--no-interaction'
    end
  end
end

after 'deploy:published', 'deploy:crontab'
```