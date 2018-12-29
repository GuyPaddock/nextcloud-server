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

/**
 * Interface for service objects that CLI processes can use to ensure that
 * config.php is owned by the correct user account.
 *
 * @package OC\Console\ConfigOwnership
 * @since 16.0.0
 */
interface IConfigOwnerEnforcer {
	/**
	 * Ensure that the current process is running under the user who owns
	 * config.php (typically the web server).
	 *
	 * If the current user or owner of the file is incorrect, and the system is
	 * configured for strict file ownership checks (which is the default
	 * behavior), an appropriate error message is written to standard error and
	 * the application exits.
	 */
	public function enforceFileOwnership();
}
