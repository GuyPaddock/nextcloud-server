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
 */

namespace OCP\Files;

use Throwable;

/**
 * Exception thrown when an existing file is not owned by the correct POSIX user
 * account.
 *
 * @package OCP\Files
 * @since 16.0.0
 */
class IncorrectFileOwnerException extends \Exception {
	/** @var string */
	private $filename;

	/** @var string */
	private $expectedOwner;

	/** @var string */
	private $actualOwner;

	/**
	 * Constructor for {@link IncorrectFileOwnerException}.
	 *
	 * @param string $message
	 *	The Exception message to throw.
	 * @param string $filename
	 *	The path to the file that is not owned by the correct user account.
	 * @param string $expectedOwner
	 *	The name of the POSIX user account that should be the owner of the
	 *	file.
	 * @param string $actualOwner
	 *	The name of the POSIX user account that actually owns the file.
	 * @param Throwable $previous [optional]
	 *	The previous throwable; used for exception chaining.
	 */
	public function __construct($message, $filename, $expectedOwner,
								$actualOwner, Throwable $previous = null) {
		parent::__construct($message, 0, $previous);

		$this->filename = $filename;
		$this->expectedOwner = $expectedOwner;
		$this->actualOwner = $actualOwner;
	}

	/**
	 * @return string
	 * 	The path to the file that is not owned by the correct user account.
	 */
	public function getFilename() {
		return $this->filename;
	}

	/**
	 * @return string
	 *	The name of the POSIX user account that should be the owner of the file.
	 */
	public function getExpectedOwner() {
		return $this->expectedOwner;
	}

	/**
	 * @return string
	 */
	public function getActualOwner() {
		return $this->actualOwner;
	}
}
