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
use OC\Files\FileOwnerVerifier;
use OCP\Files\IncorrectFileOwnerException;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * The default service object for CLI processes to ensure that config.php is
 * owned by the correct user account.
 *
 * @package OC\Console\ConfigOwnership
 * @since 16.0.0
 */
class DefaultEnforcer implements IConfigOwnerEnforcer {
	/** @var ConsoleOutputInterface */
	private $output;

	/**
	 * Constructor for {@link ConfigOwnerEnforcer}.
	 *
	 * @param ConsoleOutputInterface $output
	 *	The Symfony console output interface.
	 */
	public function __construct(ConsoleOutputInterface $output) {
		$this->output = $output;
	}

	public function enforceFileOwnership() {
		try {
			FileOwnerVerifier::verifyOwnership(
				$this->getConfigPath(),
				$this->getCurrentUserName()
			);
		} catch (IncorrectFileOwnerException $ex) {
			$this->writeAsError($ex);
			$this->exitApplication();
		}
	}

	/**
	 * @return string
	 * 	The path to the configuration file.
	 */
	protected function getConfigPath() {
		return OC::$configDir . 'config.php';
	}

	/**
	 * @return string
	 * 	Either the name of POSIX user account under which the current process is
	 * 	running; or the numeric ID for the user account, if the user's info is
	 *	not exposed in the system <code>/etc/passwd</code> file.
	 */
	protected function getCurrentUserName() {
		return FileOwnerVerifier::userIdToName(posix_getuid());
	}

	/**
	 * Exit the running application with a non-zero exit code.
	 */
	protected function exitApplication() {
		exit(1);
	}

	/**
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 * 	An instance of the Symfony error output interface.
	 */
	private function getErrorOutput() {
		return $this->output->getErrorOutput();
	}

	/**
	 * Write the details of the provided exception to standard error.
	 *
	 * @param IncorrectFileOwnerException $ex
	 *	The exception to communicate to the user.
	 */
	private function writeAsError(IncorrectFileOwnerException $ex) {
		$filename = $ex->getFilename();
		$actualOwner = $ex->getActualOwner();

		$errorOutput = $this->getErrorOutput();

		$errorOutput->writeln(
			"This command has to be executed user the user account that " .
			"owns '${filename}'."
		);

		$errorOutput->writeln('');
		$errorOutput->writeln(' Current user: ' . $ex->getExpectedOwner());
		$errorOutput->writeln('Owner of file: ' . $actualOwner);

		$errorOutput->writeln('');
		$errorOutput->writeln(
			"Please verify that the file owner is correct, or try adding " .
			"'sudo -u {$actualOwner}' to the beginning of the command " .
			"(without the single quotes)"
		);

		$optionName =
			ConfigOwnershipController::CLI_CHECK_CONFIG_OWNER_DISABLE_OPTION;

		$settingName =
			ConfigOwnershipController::CONFIG_CHECK_CONFIG_OWNER_OPTION;

		$errorOutput->writeln('');
		$errorOutput->writeln(
			"Advanced users absolutely sure permissions are correct may " .
			"override this check by passing the '--{$optionName}' option or " .
			"setting '{$settingName}' to 'false' in config.php.");
	}
}
