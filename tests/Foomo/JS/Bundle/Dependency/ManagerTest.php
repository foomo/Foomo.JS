<?php
/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\JS\Bundle\Dependency;

use Foomo\JS\Bundle\AbstractBundle;
use Foomo\JS\Bundle\Dependency;
use Foomo\JS\Bundle\MockBundles;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar <jan@bestbytes.com>
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
	public function testGetDependencyList()
	{
		// Manager::getDependencyList(MockBundles::fooBar());
		$actual = array();
		foreach(Manager::getDependencyList(MockBundles::fooBar()) as $dependency) {
			$actual[] = $dependency->bundle->name;
		}
		sort($actual);
		$this->assertEquals(
			array(
				'bar',
				'foo'
			),
			$actual,
			serialize($actual)
		);

	}
	public function testGetSortedDependencies()
	{
		$bundleFooBar = MockBundles::fooBar();
		$expected = array(
			$bundleFooBar->dependencies['bar']->bundle->dependencies['foo'],
			$bundleFooBar->dependencies['bar']
		);
		$actual = Manager::getSortedDependencies($bundleFooBar);
		$this->assertEquals(
			$expected,
			$actual
		);
	}
	public function testGetDependenciesSatisfiedByDependencies()
	{
		$bundleFoo = MockBundles::foo();
		$bundleBar = MockBundles::bar();

		$allDeps = array(
			$dependencyFoo = new Dependency($bundleFoo, Dependency::TYPE_LINK),
			$dependencyBar = new Dependency($bundleBar, Dependency::TYPE_LINK)
		);

		$dependencies = array();

		$dependencies = Manager::getDependenciesSatisfiedByDependencies($dependencies, $allDeps);

		$expected = array(
			$dependencyFoo
		);

		$this->assertEquals(
			$expected,
			$dependencies
		);

		$dependencies = Manager::getDependenciesSatisfiedByDependencies($dependencies, $allDeps);

		$expected = array(
			$dependencyFoo,
			$dependencyBar
		);

		$this->assertEquals(
			$expected,
			$dependencies
		);

	}

	public function testBundleIsSatisfiedWithDependencies()
	{
		$this->assertTrue(Manager::bundleIsSatisfiedWithDependencies(MockBundles::foo(), array()));
		$this->assertFalse(Manager::bundleIsSatisfiedWithDependencies(MockBundles::bar(), array()));
		$this->assertTrue(Manager::bundleIsSatisfiedWithDependencies(MockBundles::bar(), array(new Dependency(MockBundles::foo(), Dependency::TYPE_LINK))));
	}
}
