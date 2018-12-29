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
 * A no-op implementation of a config owner enforcer.
 *
 * This implementation is used when config owner checks are disabled.
 *
 * @package OC\Console\ConfigOwnership
 * @since 16.0.0
 */
class NoOpEnforcer implements IConfigOwnerEnforcer {
	/**
	 * Ensure that the current process is running under the user who owns
	 * config.php (typically the web server).
	 *
	 * This implementation does nothing.
	 */
	public function enforceFileOwnership() {
		// Does nothing
	}
}
