<?php

/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\JS\Bundle;

use AbstractBundle as Bundle;
use Foomo\CliCall;
use Foomo\Modules\MakeResult;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
{

	public function setup()
	{
		\Foomo\JS\Module::make('clean', new MakeResult(\Foomo\JS\Module::NAME));
	}
	public function testCompileSimpleNoDeps()
	{
		$result = Compiler::compile(MockBundles::foo());
		$this->assertInstanceOf('Foomo\\JS\\Bundle\\Compiler\\Result', $result);
		$this->assertCount(1, $result->jsFiles);
		$this->assertCount(1, $result->jsLinks);
	}

	public function testCompileDeps()
	{
		$result = Compiler::compile(MockBundles::bar());
		$this->assertCount(2, $result->jsFiles);
		$this->assertCount(2, $result->jsLinks);
		$expected = array('foo', 'bar');
		$actual = array();
		foreach($result->jsFiles as $jsFile) {
			$actual[] = substr(basename($jsFile), 0, 3);
		}
		$this->assertEquals($expected, $actual);
	}

	public function testCompileDepsProd()
	{
		$result = Compiler::compile(MockBundles::fooBar()->debug(false));
		$this->assertCount(1, $result->jsFiles);
		$this->assertCount(1, $result->jsLinks);
		$jsResult = self::runJs($result->jsFiles[0]);
		$this->assertEquals('foobarfoo', $jsResult);
	}

	private static function runJs($filename)
	{
		return trim(CliCall::create('node', array($filename))->execute()->stdOut);
	}



}