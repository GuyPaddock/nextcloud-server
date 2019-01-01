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

use OC\Console\ConfigOwnership\ConfigOwnershipController;
use OC\Console\ConfigOwnership\NoOpEnforcer;
use OC\SystemConfig;
use OCP\IConfig;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Test\TestCase;

class ConfigOwnershipControllerTest extends TestCase {
	/** @var InputInterface|PHPUnit_Framework_MockObject_MockObject */
	private $inputInterface;
	/** @var ConsoleOutputInterface|PHPUnit_Framework_MockObject_MockObject */
	private $consoleOutputInterface;
	/** @var IConfig|PHPUnit_Framework_MockObject_MockObject */
	private $config;
	/** @var SystemConfig|PHPUnit_Framework_MockObject_MockObject */
	private $systemConfig;

	public function setUp() {
		parent::setUp();

		$this->inputInterface = $this->createMock(InputInterface::class);

		$this->consoleOutputInterface =
			$this->createMock(ConsoleOutputInterface::class);

		$this->config = $this->createMock(IConfig::class);
		$this->systemConfig = $this->createMock(SystemConfig::class);
	}

	public function testRegisterOptions() {
		$inputDefinition = new InputDefinition();

		ConfigOwnershipController::registerOptions($inputDefinition);

		$this->assetTrue($inputDefinition->hasOption('no-config-owner-check'));

		$option = $inputDefinition->getOption('no-config-owner-check');

		$this->assertEquals('no-config-owner-check', $option->getName());
		$this->assertNull($option->getShortcut());
		$this->assertFalse($option->acceptValue());

		$this->assertEquals(
			'Ignore ownership of config.php during execution',
			$option->getDescription()
		);
	}

	public function testPersistSettingsWhenInputProvidesNoOption() {
		$this->withNoConfigOwnerCheckCliOptionOmitted();

		$this->systemConfig
			->expects($this->once())
			->method('setValue')
			->with(
				$this->equalTo('cli.checkconfigowner'),
				$this->equalTo(true)
			);

		ConfigOwnershipController::persistSettings(
			$this->inputInterface,
			$this->systemConfig
		);
	}

	public function testPersistSettingsWhenInputProvidesOption() {
		$this->withNoConfigOwnerCheckCliOptionProvided();

		$this->systemConfig
			->expects($this->once())
			->method('setValue')
			->with(
				$this->equalTo('cli.checkconfigowner'),
				$this->equalTo(false)
			);

		ConfigOwnershipController::persistSettings(
			$this->inputInterface,
			$this->systemConfig
		);
	}

	public function testBuildEnforcerWhenInputAndConfigBothNull() {
		$enforcer =
			ConfigOwnershipController::buildEnforcer(
				null,
				null,
				$this->consoleOutputInterface
			);

		$this->assertInstanceOf(DefaultEnforcer::class, $enforcer);
	}

	public function testBuildEnforcerForInputWhenOptionOmitted() {
		$this->withNoConfigOwnerCheckCliOptionOmitted();

		$enforcer =
			ConfigOwnershipController::buildEnforcer(
				$this->inputInterface,
				null,
				$this->consoleOutputInterface
			);

		$this->assertInstanceOf(DefaultEnforcer::class, $enforcer);
	}

	public function testBuildEnforcerForInputWhenOptionProvided() {
		$this->withNoConfigOwnerCheckCliOptionProvided();

		$enforcer =
			ConfigOwnershipController::buildEnforcer(
				$this->inputInterface,
				null,
				$this->consoleOutputInterface
			);

		$this->assertInstanceOf(NoOpEnforcer::class, $enforcer);
	}

	public function testBuildEnforcerForConfigWhenOptionFalse() {
		$this->withCheckConfigOwnerConfigOptionFalse();

		$enforcer =
			ConfigOwnershipController::buildEnforcer(
				null,
				$this->config,
				$this->consoleOutputInterface
			);

		$this->assertInstanceOf(NoOpEnforcer::class, $enforcer);
	}

	public function testBuildEnforcerForConfigWhenOptionTrue() {
		$this->withCheckConfigOwnerConfigOptionTrue();

		$enforcer =
			ConfigOwnershipController::buildEnforcer(
				null,
				$this->config,
				$this->consoleOutputInterface
			);

		$this->assertInstanceOf(DefaultEnforcer::class, $enforcer);
	}

	public function testBuildEnforcerForInputAndConfigWhenInputVetoes() {
		$this->withNoConfigOwnerCheckCliOptionProvided();
		$this->withCheckConfigOwnerConfigOptionTrue();

		$enforcer =
			ConfigOwnershipController::buildEnforcer(
				$this->inputInterface,
				$this->config,
				$this->consoleOutputInterface
			);

		$this->assertInstanceOf(NoOpEnforcer::class, $enforcer);
	}

	public function testBuildEnforcerForInputAndConfigWhenConfigVetoes() {
		$this->withNoConfigOwnerCheckCliOptionOmitted();
		$this->withCheckConfigOwnerConfigOptionFalse();

		$enforcer =
			ConfigOwnershipController::buildEnforcer(
				$this->inputInterface,
				$this->config,
				$this->consoleOutputInterface
			);

		$this->assertInstanceOf(NoOpEnforcer::class, $enforcer);
	}

	public function testBuildEnforcerForInputAndConfigWhenBothVeto() {
		$this->withNoConfigOwnerCheckCliOptionProvided();
		$this->withCheckConfigOwnerConfigOptionFalse();

		$enforcer =
			ConfigOwnershipController::buildEnforcer(
				$this->inputInterface,
				$this->config,
				$this->consoleOutputInterface
			);

		$this->assertInstanceOf(DefaultEnforcer::class, $enforcer);
	}

	private function withNoConfigOwnerCheckCliOptionOmitted() {
		// No op -- defined for test clarity
	}

	private function withNoConfigOwnerCheckCliOptionProvided() {
		$this->inputInterface
			->expects($this->once())
			->method('getOption')
			->with('no-config-owner-check')
			->willReturn(true);
	}

	private function withCheckConfigOwnerConfigOptionTrue() {
		$this->config
			->expects($this->once())
			->method('getSystemValue')
			->with('cli.checkconfigowner')
			->willReturn(true);
	}

	private function withCheckConfigOwnerConfigOptionFalse() {
		$this->config
			->expects($this->once())
			->method('getSystemValue')
			->with('cli.checkconfigowner')
			->willReturn(false);
	}
}
