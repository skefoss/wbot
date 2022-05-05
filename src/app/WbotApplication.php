<?php

namespace Wbot\Application;

use Symfony\Component\Console\Application;

/**
 * Class WbotApplication
 * @package Wbot\Application
 */
class WbotApplication extends Application {
	public function __construct(string $name = 'Wbot') {
		parent::__construct($name, '0.0.0');
	}
}
