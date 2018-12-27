<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Ko- <k.stoffelen@cs.ru.nl>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Patrick Paysant <patrick.paysant@linagora.com>
 * @author RealRancor <fisch.666@gmx.de>
 * @author Robin Appelman <robin@icewind.nl>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
 * @author Guy Elsmore-Paddock <guy@inveniem.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

require_once __DIR__ . '/lib/versioncheck.php';

use OC\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

define('OC_CONSOLE', 1);

function exceptionHandler($exception) {
	echo "An unhandled exception has been thrown:" . PHP_EOL;
	echo $exception;
	exit(1);
}

/**
 * Ensure that the configuration file is owned by the same user as the account
 * running the CLI.
 */
function checkConfigOwner() {
	if (!function_exists('posix_getuid')) {
		echo "The posix extensions are required - see " .
				"http://php.net/manual/en/book.posix.php" . PHP_EOL;

		exit(1);
	}

	$user = posix_getpwuid(posix_getuid());
	$configUser = posix_getpwuid(fileowner(OC::$configDir . 'config.php'));

	if ($user['name'] !== $configUser['name']) {
		echo "Console has to be executed with the user that owns the file " .
				"config/config.php" . PHP_EOL;
		echo PHP_EOL;
		echo "Current user: " . $user['name'] . PHP_EOL;
		echo "Owner of config.php: " . $configUser['name'] . PHP_EOL;
		echo "Try adding 'sudo -u " . $configUser['name'] . " ' to the " .
				"beginning of the command (without the single quotes)" .
				PHP_EOL;
		echo PHP_EOL;
		echo "Advanced users absolutely sure permissions are correct may " .
				"override this check by passing --no-config-owner-check" .
				PHP_EOL;

		exit(1);
	}
}

try {
	require_once __DIR__ . '/lib/base.php';

	// set to run indefinitely if needed
	if (strpos(@ini_get('disable_functions'), 'set_time_limit') === false) {
		@set_time_limit(0);
	}

	if (!OC::$CLI) {
		echo "This script can be run from the command line only" . PHP_EOL;
		exit(1);
	}

	set_exception_handler('exceptionHandler');

	$shouldCheckConfigOwner = !in_array('--no-config-owner-check', $argv);
	if ($shouldCheckConfigOwner) {
		checkConfigOwner();
	}

	$oldWorkingDir = getcwd();
	if ($oldWorkingDir === false) {
		echo "This script can be run from the Nextcloud root directory only." . PHP_EOL;
		echo "Can't determine current working dir - the script will continue to work but be aware of the above fact." . PHP_EOL;
	} else if ($oldWorkingDir !== __DIR__ && !chdir(__DIR__)) {
		echo "This script can be run from the Nextcloud root directory only." . PHP_EOL;
		echo "Can't change to Nextcloud root directory." . PHP_EOL;
		exit(1);
	}

	if (!function_exists('pcntl_signal') && !in_array('--no-warnings', $argv)) {
		echo "The process control (PCNTL) extensions are required in case you want to interrupt long running commands - see http://php.net/manual/en/book.pcntl.php" . PHP_EOL;
	}

	$config = \OC::$server->getConfig();
	$config->setSystemValue('cli.checkconfigowner', $shouldCheckConfigOwner);

	$application = new Application(
		$config,
		\OC::$server->getEventDispatcher(),
		\OC::$server->getRequest(),
		\OC::$server->getLogger(),
		\OC::$server->query(\OC\MemoryInfo::class)
	);
	$application->loadCommands(new ArgvInput(), new ConsoleOutput());
	$application->run();
} catch (Exception $ex) {
	exceptionHandler($ex);
} catch (Error $ex) {
	exceptionHandler($ex);
}
