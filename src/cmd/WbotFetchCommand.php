<?php
namespace Wbot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class WbotFetchCommand extends Command {
	protected function configure() {
		$this->setName('fetch')
			->setDescription('Fetch files')
			->addOption('out', 'o', InputArgument::OPTIONAL, 'Output directory', getcwd() . DIRECTORY_SEPARATOR . 'out')
			->addArgument('path', InputArgument::REQUIRED|InputArgument::IS_ARRAY, 'Path to fetch')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$paths = $input->getArgument('path');
		$outDir = $input->getOption('out');
		foreach ($paths as $path) {
			$output->writeln("Fetching $path...");
			if (!(filter_var($path, \FILTER_VALIDATE_URL) ? $this->fetchUrl($path, $outDir, $output) : $this->fetchFile($path, $outDir, $output))) {
				$output->writeln('<error>Failed to fetch ' . $path . '</error>');
			}
			$output->writeln("$path fetched!");
		}
		return Command::SUCCESS;
	}

	public function fetchUrl(string $url, string $outDir, OutputInterface $output): bool {
		$info = parse_url($url);

		$scheme = $info['scheme'] ?? 'http';
		$host = $info['host'] ?? 'localhost';
		$port = $info['port'] ?? 80;
		$path = $info['path'] ?? '/';
		$query = $info['query'] ?? '';
		$user = $info['user'] ?? '';
		$pass = $info['pass'] ?? '';

		$url = strtolower($scheme) . '://' . ($user ? $user . ':' . $pass . '@' : '') . strtolower($host) . ($port ? ':' . $port : '') . strtolower($path) . ($query ? '?' . $query : '');
		$method = 'GET';
		$headers = [
			'Content-Type' => 'application/x-www-form-urlencoded',
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
			'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
			'Accept-Encoding' => 'gzip, deflate, br',
			'Accept-Language' => 'en-US,en;q=0.9',
			'Connection' => 'keep-alive',
			'Upgrade-Insecure-Requests' => '1',
			'Cache-Control' => 'max-age=0',
			'TE' => 'Trailers',
			'DNT' => '1',
			'Pragma' => 'no-cache',
			'If-Modified-Since' => 'Thu, 01 Jan 1970 00:00:00 GMT',
			'If-None-Match' => '"5c6c-1c1c-2c3c-4c5c"',
			'Cookie' => '',
			'X-Requested-With' => 'XMLHttpRequest',
			'Referer' => $url,
			'Origin' => $url,
			'Sec-Fetch-Mode' => 'cors',
			'Sec-Fetch-Site' => 'same-origin',
			'Sec-Fetch-User' => '?1',
		];

		$context = stream_context_create([
			$scheme => [
				'follow_location' => 0,
				'method' => $method,
				'header' => implode(PHP_EOL, array_map(fn($k, $v) => $k . ': ' . $v, array_keys($headers), array_values($headers))),
			],
		]);

		$output->writeln("Getting url $url...");
		$contents = $this->getContent($url, $context);
		if (false === $contents) {
			$output->writeln('<error>Failed to get url ' . $url . '</error>');
			return false;
		}
		$output->writeln("Getted url $url!");

		$outFile = $outDir . DIRECTORY_SEPARATOR . $host;
		if (!$path || str_ends_with($path, '/')) {
			$outFile .= $path . 'index.php';
		} else {
			if (empty(pathinfo($path, PATHINFO_EXTENSION))) {
				$path .= '.php';
			}
			$outFile .= $path;
		}

		$output->writeln("Putting to $outFile...");
		if (!$this->putContent($outFile, $contents)) {
			$output->writeln('<error>Failed to put to ' . $outFile . '</error>');
			return false;
		}
		$output->writeln("Putted to $outFile!");
		return true;
	}

	public function fetchFile(string $path, string $outDir, OutputInterface $output) {
		$context = stream_context_create([
			'stream' => [
				'follow_location' => 0,
			],
		]);

		$output->writeln("Getting file $path...");
		$contents = $this->getContent($path, $context);
		if (false === $contents) {
			$output->writeln('<error>Failed to get file ' . $path . '</error>');
			return false;
		}
		$output->writeln("Getted file $path!");

		$outFile = $outDir . DIRECTORY_SEPARATOR . $path;
		$output->writeln("Putting to $outFile...");
		if (!$this->putContent($outFile, $contents)) {
			$output->writeln('<error>Failed to put to ' . $outFile . '</error>');
			return false;
		}
		$output->writeln("Putted to $outFile!");
		return true;
	}

	public function getContent(string $path, $context = null): string|false {
		if (null === $context) {
			$context = stream_context_create();
		}
		return file_get_contents($path, false, $context);
	}

	public function putContent(string $path, string $contents): bool {
		if (!file_exists(dirname($path))) {
			mkdir(dirname($path), 0777, true);
		}
		return file_put_contents($path, $contents, LOCK_EX);
	}
}
