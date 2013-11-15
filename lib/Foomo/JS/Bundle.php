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

namespace Foomo\JS;
use Foomo\JS\Bundle\Compiler\Result;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar <jan@bestbytes.com>
 */
class Bundle extends Bundle\AbstractBundle
{
	/**
	 * @var string[]
	 */
	public $javaScripts = array();

	/**
	 * @param string $script
	 * @return \Foomo\TypeScript\Bundle
	 */
	public function addJavascript($script)
	{
		return $this->addEntryToPropArray($script, 'javaScripts');
	}

	/**
	 * @param string[] $scripts
	 * @return \Foomo\TypeScript\Bundle
	 */
	public function addJavaScripts(array $scripts)
	{
		return $this->addEntriesToPropArray($scripts, 'javaScripts');
	}

	/**
	 * @param bool $debug
	 * @return \Foomo\TypeScript\Bundle
	 */
	public function debug($debug)
	{
		$this->debug = $debug;
		return $this;
	}

	public function getJSLinks()
	{
		return array($this->jsCompiler->getOutputPath());
	}

	public function getJSFiles()
	{
		return array($this->jsCompiler->getOutputFilename());
	}
	public function compile(Result $result)
	{
		$jsCompiler = \Foomo\JS::create($this->javaScripts)
			->compress(!$this->debug)
			->name($this->name)
			->compile()
		;
		$result->jsFiles[] = $jsCompiler->getOutputFilename();
		$result->jsLinks[] = $jsCompiler->getOutputPath();
		return $this;
	}

	/**
	 * @param string $name
	 * @return Bundle
	 */
	public static function create($name)
	{
		return parent::create($name);
	}
}
