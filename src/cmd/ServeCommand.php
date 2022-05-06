<?php
namespace Ske\Bot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ServeCommand extends Command {
	protected function configure() {
		$this->setName('serve')
			->setDescription('Serve files')
			->addOption('port', 'P', InputArgument::OPTIONAL, 'Port to serve on', 80)
			->addOption('host', 'H', InputArgument::OPTIONAL, 'Host to serve on', 'localhost')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$host = $input->getOption('host');
		$port = $input->getOption('port');
		$dir = RES_DIR;
		if (!is_dir($dir)) {
			$output->writeln("Making directory $dir");
			if (!mkdir($dir, 0777, true)) {
				$output->writeln("Could not make directory $dir");
				return;
			}
			$output->writeln("$dir directory maked");
		}

		exec(PHP_BINARY . " -S $host:$port -t $dir", $lines, $status);

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
