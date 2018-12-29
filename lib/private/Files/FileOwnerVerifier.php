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

namespace OC\Files;

use OCP\Files\IncorrectFileOwnerException;

/**
 * A service object for verifying that a file is owned by the expected POSIX
 * user account.
 *
 * @package OC\Console
 * @since 16.0.0
 */
class FileOwnerVerifier {
	/**
	 * Private constructor for static class.
	 */
	private function __construct() { }

	/**
	 * Verify that a file is owned by the desired target owner.
	 *
	 * If the target file is incorrect, an exception is thrown.
	 *
	 * @param string $filename
	 *   The path to the file that is being verified.
	 * @param string $expectedOwner
	 *   The name of the POSIX user account that is expected to be the owner of
	 *   the file.
	 *
	 * @throws IncorrectFileOwnerException
	 *   If the file is not owned by the target owner and the system is
	 *   configured for strict file ownership checks.
	 */
	public static function verifyOwnership($filename, $expectedOwner) {
		self::ensurePosixExtensionsAreInstalled();

		$actualOwner = self::userIdToName(fileowner($filename));

		if ($actualOwner !== $expectedOwner) {
			throw new IncorrectFileOwnerException(
				sprintf(
					'The owner of the file "%s" must be "%s" but it is ' .
					'currently "%s".',
					$filename,
					$expectedOwner,
					$actualOwner
				),
				$filename,
				$expectedOwner,
				$actualOwner
			);
		}
	}

	/**
	 * Convert a POSIX user ID to POSIX user name.
	 *
	 * @param integer $userId
	 * 	The numeric identifier for the user account.
	 *
	 * @return string
	 * 	Either the name of the given POSIX user account; or the numeric ID for
	 *	the user account, if the user's info is not exposed in the
	 * 	system <code>/etc/passwd</code> file.
	 */
	public static function userIdToName($userId) {
		$userInfo = posix_getpwuid($userId);

		// On platforms that use a chroot for each environment (e.g. Virtuozzo),
		// processes can be running under user IDs that do not appear within the
		// passwd file of the current chroot environment. Files can also be
		// owned by such users. In these cases, posix_getpwuid() returns null.
		if (isset($userInfo['name'])) {
			return $userInfo['name'];
		} else {
			return $userId;
		}
	}

	/**
	 * Checks that the <code>posix_getuid()</code> function is available to us.
	 */
	private static function ensurePosixExtensionsAreInstalled() {
		if (!function_exists('posix_getuid')) {
			throw new \RuntimeException(
				"The POSIX extensions for PHP are required - see " .
				"http://php.net/manual/en/book.posix.php"
			);
		}
	}
}
