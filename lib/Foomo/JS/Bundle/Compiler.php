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
use Foomo\JS;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class Compiler
{
	/**
	 * @param AbstractBundle $bundle
	 *
	 * @return Compiler\Result
	 */
	public static function compile(AbstractBundle $bundle)
	{
		$dependencies = Dependency\Manager::getSortedDependencies($bundle);
		$dependencies[] = new Dependency($bundle, Dependency::TYPE_LINK);
		foreach($dependencies as $dependency) {
			$dependency->compile();
		}
		$result = new Compiler\Result();
		self::link(end($dependencies), $result, $bundle->debug);
		if(!$bundle->debug) {
			for($i = 0;$i < count($result->jsFiles); $i ++) {
				$jsFiles = $result->jsFiles[$i];
				if(is_array($jsFiles)) {
					$name = 'merge-' . md5(implode('-', $jsFiles));
					$basename =  $name . '.js';
					$filename = \Foomo\JS\Module::getHtdocsVarDir() . DIRECTORY_SEPARATOR . $basename;
					if(!file_exists($filename)) {
						$jsCompiler = JS::create($jsFiles)
							->name($name)
							->compress()
							->compile()
						;
						rename($jsCompiler->getOutputFilename(), $filename);
					}
					$result->jsFiles[$i] = $filename;
					$result->jsLinks[$i] = array(\Foomo\JS\Module::getHtdocsVarBuildPath($basename));
				}
			}
		}
		return $result;
	}
	public static function runCompiler(AbstractBundle $bundle)
	{

	}

	public static function link(Dependency $dependency, JS\Bundle\Compiler\Result $result, $debug)
	{
		foreach($dependency->bundle->dependencies as $parentDependency) {
			self::link($parentDependency, $result, $debug);
		}
		if($dependency->type == Dependency::TYPE_MERGE && !$debug) {
			$result->jsFiles[] = $dependency->result->jsFiles;
			$result->jsLinks[] = '-- waiting for merge --';
		} else {
			// link
			$result->jsFiles = array_merge($result->jsFiles, $dependency->result->jsFiles);
			$result->jsLinks = array_merge($result->jsLinks, $dependency->result->jsLinks);
		}
	}
}