<?php
namespace Ske\Bot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Ske\Bot\Application\Client;

class FetchCommand extends Command {
	protected function configure() {
		$this
			->setName('fetch')
			->setDescription('Fetch files')
			->addArgument('path', InputArgument::REQUIRED|InputArgument::IS_ARRAY, 'Path to fetch')
			->addOption('--tmp', null, InputOption::VALUE_NONE, 'Use tmp directory')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$paths = $input->getArgument('path');
		$client = new Client($input->getOption('tmp') ? TMP_DIR : OUT_DIR);
		return $client->fetch($paths, $output);
	}
}
