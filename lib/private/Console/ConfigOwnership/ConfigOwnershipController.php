<?php
/**
 * @copyright 2018 Inveniem <guy@inveniem.com>
 *
 * @author Guy Elsmore-Paddock <guy@inveniem.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OC\Console\ConfigOwnership;

use OC;
use OC\SystemConfig;
use OCP\IConfig;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * A controller that assists CLI commands in ensuring that the application
 * configuration file is owned by the correct user account and that CLI is
 * being run by the same user as the file owner.
 *
 * @package OC\Console\ConfigOwnership
 * @since 16.0.0
 */
class ConfigOwnershipController {
	/**
	 * The name of the option that can be passed on CLI to disable config.php
	 * ownership checks.
	 *
	 * @var string
	 */
	const CLI_CHECK_CONFIG_OWNER_DISABLE_OPTION = 'no-config-owner-check';

	/**
	 * The name of the option that can be specified in 'config.php' to disable
	 * config.php ownership checks.
	 *
	 * @var string
	 */
	const CONFIG_CHECK_CONFIG_OWNER_OPTION = 'cli.checkconfigowner';

	/**
	 * Private constructor for static class.
	 */
	private function __construct() { }

	/**
	 * Define all of the CLI/input option(s) this factory handles.
	 *
	 * @param InputDefinition $inputDefinition
	 *	The Symfony input definition object, used to control what options are
	 * 	acceptable.
	 */
	public static function registerOptions(InputDefinition $inputDefinition) {
		$inputDefinition->addOption(
			new InputOption(
				self::CLI_CHECK_CONFIG_OWNER_DISABLE_OPTION,
				null,
				InputOption::VALUE_NONE,
				'Ignore ownership of config.php during execution'
			)
		);
	}

	/**
	 * Update the system configuration to denote whether config permission
	 * checking has been disabled in this NextCloud installation.
	 *
	 * @param InputInterface $srcInput
	 * 	The Symfony input interface from which options should be obtained (this
	 *	is typically an abstraction of CLI).
	 * @param SystemConfig $destConfig
	 *	The system configuration to which the settings will be persisted.
	 */
	public static function persistSettings(InputInterface $srcInput,
										   SystemConfig $destConfig) {
		$destConfig->setValue(
			self::CONFIG_CHECK_CONFIG_OWNER_OPTION,
			self::hasInputSuppressedChecks($srcInput));
	}

	/**
	 * Create the appropriate config file ownership enforcer implementation to
	 * use based on options provided on th command-line and/or in the
	 * application config.
	 *
	 * @param InputInterface|null $input
	 * 	The Symfony input interface from which options should be obtained (this
	 *	is typically an abstraction of CLI). If provided, this takes precedence
	 * 	over values provided by the application configuration. <code>null</code>
	 * 	can be provided if input options should be disregarded.
	 * @param IConfig|null $config
	 *	The application configuration. If provided, the application
	 * 	configuration is consulted to determine if config ownership checks have
	 * 	been disabled on a system-wide basis. <code>null</code> can be provided
	 * 	if config options should be disregarded.
	 * @param ConsoleOutputInterface $output
	 *	The Symfony console output interface, to which errors about config file
	 *	ownership should be written.
	 *
	 * @return IConfigOwnerEnforcer
	 *	The enforcer instance to use for checking file ownership.
	 *
	 * @noinspection PhpOptionalBeforeRequiredParametersInspection
	 */
	public static function buildEnforcer(InputInterface $input = null,
										 IConfig $config = null,
										 ConsoleOutputInterface $output) {
		if (self::hasInputSuppressedChecks($input)
			|| self::hasConfigSuppressedChecks($config)) {
			return new NoOpEnforcer();
		} else {
			return new DefaultEnforcer($output);
		}
	}

	/**
	 * Determine whether an input interface was provided that suppresses config
	 * file ownership checks.
	 *
	 * @param InputInterface|null $input
	 * 	The input interface from which to inspect options.
	 *
	 * @return bool
	 * 	<code>true</code> only if an input interface has been provided that
	 * 	suppresses file ownership checks.
	 */
	private static function hasInputSuppressedChecks(
												InputInterface $input = null) {
		return ($input !== null)
			&& $input->getOption(self::CLI_CHECK_CONFIG_OWNER_DISABLE_OPTION);
	}

	/**
	 * Determine whether a config interface was provided that suppresses config
	 * file ownership checks.
	 *
	 * @param IConfig|null $config
	 * 	The config interface from which to inspect values.
	 *
	 * @return bool
	 * 	<code>true</code> only if a config interface has been provided that has
	 * 	the option for config ownership checking set to <code>false</code>.
	 */
	private static function hasConfigSuppressedChecks(IConfig $config = null) {
		if ($config !== null) {
			$configValue =
				$config->getSystemValue(
					self::CONFIG_CHECK_CONFIG_OWNER_OPTION,
					true
				);

			return ($configValue === false);
		} else {
			return false;
		}
	}
}
