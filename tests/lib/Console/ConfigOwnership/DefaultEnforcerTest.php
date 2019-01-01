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

namespace Test\Console\ConfigOwnership;

use OC;
use PHPUnit_Framework_MockObject_MockObject;
use Test\TestCase;

class DefaultEnforcerTest extends TestCase {
	const MOCK_CONFIG_DIR_NAME = 'configDir';

	/** @var string */
	private $oldConfigDir;

	/** @var DefaultEnforcer */
	private $enforcer;

	public function setUp() {
		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(
			new vfsStreamDirectory(self::MOCK_CONFIG_DIR_NAME)
		);

		$this->oldConfigDir = OC::$configDir;
		OC::$configDir = vfsStream::url(self::MOCK_CONFIG_DIR_NAME);
	}

	public function tearDown() {
		OC::$configDir = $this->oldConfigDir;
	}

	public function testEnforceFileOwnershipWhenOwnerIsWrong() {
	}

	public function testEnforceFileOwnershipWhenOwnerIsCorrect() {
	}
}
