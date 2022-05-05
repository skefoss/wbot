<?php
namespace Wbot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class WbotServeCommand extends Command {
	protected function configure() {
		$this->setName('serve')
			->setDescription('Serve files')
			->addOption('port', 'P', InputArgument::OPTIONAL, 'Port to serve on', 80)
			->addOption('host', 'H', InputArgument::OPTIONAL, 'Host to serve on', 'localhost')
			->addOption('dir', 'D', InputArgument::OPTIONAL, 'Directory to serve', getcwd() . DIRECTORY_SEPARATOR . 'out')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$host = $input->getOption('host');
		$port = $input->getOption('port');
		$dir = $input->getOption('dir');
		if (!is_dir($dir)) {
			$output->writeln("Making directory $dir");
			if (!mkdir($dir, 0777, true)) {
				$output->writeln("Could not make directory $dir");
				return;
			}
			$output->writeln("$dir directory maked");
		}
		if (!is_file($indexFile = $dir . DIRECTORY_SEPARATOR . 'index.php')) {
			$output->writeln("Making index file $indexFile");
			$content = '<?php';
			$files = scandir($dir);
			foreach ($files as $file) {
				if ($file == '.' || $file == '..') {
					continue;
				}

				$content .= '<p><a href="' . $file . '">' . $file . '</a></p>';
			}
			if (!file_put_contents($indexFile, $content, LOCK_EX)) {
				$output->writeln("Could not make index file $indexFile");
				return;
			}
			$output->writeln("$indexFile file maked");
		}

		if (!is_file($htaccessFile = $dir . DIRECTORY_SEPARATOR . '.htaccess')) {
			$output->writeln("Making htaccess file $htaccessFile");
			$content = '<IfModule mod_rewrite.c>' . PHP_EOL;
			$content .= 'RewriteEngine On' . PHP_EOL;
			$content .= 'RewriteRule ^(.*)$ index.php?file=$1 [L]' . PHP_EOL;
			$content .= '</IfModule>' . PHP_EOL;
			if (!file_put_contents($htaccessFile, $content, LOCK_EX)) {
				$output->writeln("Could not make htaccess file $htaccessFile");
				return;
			}
			$output->writeln("$htaccessFile file maked");
		}

		$output->writeln("Serving files from $dir on $host:$port");
		exec(PHP_BINARY . " -S $host:$port -t " . getcwd() . DIRECTORY_SEPARATOR . 'out', $lines, $status);

		if (0 != $status) {
			$output->writeln("Could not serve files from $dir on $host:$port");
			if (count($lines) > 0) {
				if (preg_match('/^.*\(reason: (?<reason>.*)\)$/', $lines[count($lines) - 1], $matches)) {
					$output->writeln("Reason: {$matches['reason']}");
				}
				else {
					$output->writeln("Reason: {$lines[count($lines) - 1]}");
				}
			}
			return Command::FAILURE;
		}
		else {
			$output->writeln("Files served from $dir on $host:$port");
			$output->writeln("Press Ctrl+C to stop");
			foreach ($lines as $line) {
				$output->writeln($line);
			}
			return Command::SUCCESS;
		}
	}
}
