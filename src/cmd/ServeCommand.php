<?php
namespace Ske\Bot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Ske\Bot\Application\Server;

class ServeCommand extends Command {
	protected function configure() {
		$this->setName('serve')
			->setDescription('Serve files')
			->addOption('port', 'P', InputArgument::OPTIONAL, 'Port to serve on', 80)
			->addOption('host', 'H', InputArgument::OPTIONAL, 'Host to serve on', 'localhost')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$host = $input->getOption('host');
		$port = $input->getOption('port');
		$server = new Server($host, $port, RES_DIR);
		return $server->serve($output);
	}
}
