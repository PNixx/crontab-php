<?php
namespace PNixx\Crontab;

class Crontab {

	/**
	 * @var Job[]
	 */
	protected $jobs = [];

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string|null
	 */
	protected $crontab = null;

	/**
	 * @var string
	 */
	protected $block_open;

	/**
	 * @var string
	 */
	protected $block_close;

	/**
	 * Crontab constructor.
	 * @param string      $block_name Your project name, branch or other name
	 * @param string      $path       Root directory your project (checking with realpath method).
	 *                                Will be append for all jobs
	 * @param string|null $user       Configure for this user name, require sudo privileges
	 */
	public function __construct($block_name, $path = null, $user = null) {
		$this->path = realpath($path);

		$this->block_open = '#===BEGIN Crontab for project: ' . $block_name;
		$this->block_close = '#===END Crontab for project: ' . $block_name;
	}

	/**
	 * @param Job $job
	 */
	public function add(Job $job) {
		$job->setPath($this->path);
		$this->jobs[] = $job;
	}

	/**
	 * Append to crontab jobs
	 */
	public function update() {
		$this->read();
		$this->clearBlock();

		$this->crontab .= $this->generate();

		$this->save();
	}

	/**
	 * Remove all jobs from crontab
	 */
	public function remove() {
		$this->read();
		$this->clearBlock();
		$this->save();
	}

	/**
	 * Read current crontab file
	 */
	private function read() {
		$f = popen('crontab -l 2> /dev/null', 'r');
		while(!feof($f)) {
			$this->crontab .= fgets($f, 1024);
			flush();
		}
		fclose($f);
	}

	private function save() {
		$f = popen('crontab -', 'r+');
		fwrite($f, $this->crontab);
		fclose($f);
	}

	/**
	 * Generate new block
	 * @return string
	 */
	private function generate() {
		return implode(PHP_EOL, [PHP_EOL, $this->block_open, implode(PHP_EOL, $this->jobs), $this->block_close]) . PHP_EOL;
	}

	/**
	 * Clear old block
	 * @throws Exception
	 */
	private function clearBlock() {
		if( $this->crontab ) {
			if( stristr($this->crontab, $this->block_open) && !stristr($this->crontab, $this->block_close) ) {
				throw new Exception('Unclosed indentifier; Your crontab file contains \'' . $this->block_open . '\', but no \'' . $this->block_close . '\'');
			}
			if( !stristr($this->crontab, $this->block_open) && stristr($this->crontab, $this->block_close) ) {
				throw new Exception('Unopened indentifier; Your crontab file contains \'' . $this->block_close . '\', but no \'' . $this->block_open . '\'');
			}
			$this->crontab = preg_replace('/\s*' . preg_quote($this->block_open) . '\s*.+?' . preg_quote($this->block_close) . '\s*/s', PHP_EOL, $this->crontab);
		}
	}
}