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

namespace Foomo\JS\Bundle;
use Foomo\JS\Bundle\Compiler\Result;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar <jan@bestbytes.com>
 */
abstract class AbstractBundle
{

	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var bool
	 */
	public $debug;
	/**
	 * @var Dependency[]
	 */
	public $dependencies = array();

	protected function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * @param string $name
	 *
	 * @return AbstractBundle
	 */
	public static function create($name)
	{
		$calledClass = get_called_class();
		return new $calledClass($name);
	}

	/**
	 * merge with another bundle
	 *
	 * @param AbstractBundle $bundle
	 *
	 * @return AbstractBundle
	 */
	public function merge(AbstractBundle $bundle)
	{
		// make sure thins are not duplicate - check bundle name
		$this->dependencies[$bundle->name] = new Dependency($bundle, Dependency::TYPE_MERGE);
		return $this;
	}
	/**
	 * @param AbstractBundle $bundle
	 *
	 * @return AbstractBundle
	 */
	public function addDependency(AbstractBundle $bundle)
	{
		$this->dependencies[$bundle->name] = new Dependency($bundle, Dependency::TYPE_LINK);
		return $this;
	}

	/**
	 * @param bool $debug
	 * @return AbstractBundle
	 */
	public function debug($debug)
	{
		$this->debug = $debug;
		return $this;
	}
	/**
	 * @param array $entries
	 * @param string $propArrayName
	 *
	 * @return AbstractBundle
	 */
	protected function addEntriesToPropArray(array $entries, $propArrayName)
	{
		foreach ($entries as $entry) {
			$this->addEntryToPropArray($entry, $propArrayName);
		}
		return $this;
	}

	/**
	 * @param string $entry
	 * @param string $propArrayName
	 * @return AbstractBundle
	 */
	protected function addEntryToPropArray($entry, $propArrayName)
	{
		// todo maybe remove this one later ... ?!
		if(!isset($this->{$propArrayName}) || !is_array($this->{$propArrayName})) {
			trigger_error('that is not a valid proparray '  . $propArrayName, E_USER_ERROR);
		}
		if (!in_array($entry, $this->{$propArrayName})) {
			$this->{$propArrayName}[] = $entry;
		}
		return $this;
	}


	/**
	 * @param AbstractBundle[] $bundles
	 *
	 * @return AbstractBundle
	 */
	public function addDependencies(array $bundles)
	{
		return $this->addEntriesToPropArray($bundles, 'dependencies');
	}

	/**
	 * compile things, that need to be compiled
	 *
	 * @param Result $result
	 *
	 * @return AbstractBundle
	 */
	abstract public function compile(Result $result);


}
