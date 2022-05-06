<?php

namespace Ske\Bot\Application;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Server
 *
 * @package Ske\Bot\Application
 */
class Server {
	/**
	 * @param string $host
	 * @param int    $port
	 * @param string $path
	 */
	public function __construct(string $host, int $port, string $path) {
		$this->setHost($host);
		$this->setPort($port);
		$this->setPath($path);
	}

	/**
	 * @var string
	 */
	protected string $host = '';

	/**
	 * @param string $host
	 * @return self
	 */
	public function setHost(string $host): self {
		$this->host = $host;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getHost(): string {
		return $this->host;
	}

	/**
	 * @var int
	 */
	protected int $port = 0;

	/**
	 * @param int $port
	 * @return self
	 */
	public function setPort(int $port): self {
		$this->port = $port;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPort(): int {
		return $this->port;
	}

	/**
	 * @var string
	 */
	protected string $path = '';

	/**
	 * @param string $path
	 * @return self
	 */
	public function setPath(string $path): self {
		$this->path = $path;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}

	/**
	 * @return void
	 */
	public function serve(OutputInterface $output): int {
		$host = $this->getHost() ?: 'localhost';
		$port = $this->getPort() ?: 80;
		$path = $this->getPath();

		$command = PHP_BINARY . " -S $host:$port";

		if (file_exists($path)) {
			$command .= ' ' . is_dir($path) ? " -t $path" : $path;
		}

		exec($command, $lines, $status);

		if (0 != $status) {
			$output->writeln("Could not serve files on $host:$port" . ($path ? " in $path" : ''));
			if (count($lines) > 0) {
				if (preg_match('/^.*\(reason: (?<reason>.*)\)$/', $lines[count($lines) - 1], $matches)) {
					$output->writeln("Reason: {$matches['reason']}");
				}
				else {
					$output->writeln("Reason: {$lines[count($lines) - 1]}");
				}
			}
			return 1;
		}
		else {
			$output->writeln("Files served on $host:$port" . ($path ? " in $path" : ''));
			$output->writeln("Press Ctrl+C to stop");
			foreach ($lines as $line) {
				$output->writeln($line);
			}
			return 0;
		}
	}
}
