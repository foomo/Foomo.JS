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
use Foomo\Cache\Proxy;
use Foomo\Config;
use Foomo\HTMLDocument;
use Foomo\JS;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class Compiler
{
	/**
	 * @param mixed $bundleProvider
	 * @param array $bundleProviderArguments
	 * @param bool $debug
	 * @return Compiler\Result|mixed
	 */
	public static function compileAndCache($bundleProvider, array $bundleProviderArguments = array(), $debug = null)
	{
		if(is_null($debug)) {
			$debug = !Config::isProductionMode();
		}
		if(!$debug) {
			return Proxy::call(__CLASS__, 'cachedCompileBundle', array($bundleProvider, $bundleProviderArguments));
		} else {
			return self::compileBundleUsingProvider($bundleProvider, $bundleProviderArguments);
		}
	}
	/**
	 * @param string $bundleProvider string class::method
	 * @param array $bundleProviderArguments
	 * @param HTMLDocument $doc
	 * @param bool $debug
	 * @throws \InvalidArgument
	 */
	public static function addBundleToDoc($bundleProvider, array $bundleProviderArguments = array(), HTMLDocument $doc = null, $debug = null)
	{
		if(is_null($doc)) {
			$doc = HTMLDocument::getInstance();
		}
		$doc->addJavascriptsToBody(self::compileAndCache($bundleProvider, $bundleProviderArguments, $debug)->jsLinks);
	}

	/**
	 * @param string $bundleProvider
	 * @param array $bundleProviderArguments
	 *
	 * @return Compiler\Result
	 *
	 * @Foomo\Cache\CacheResourceDescription
	 */
	public static function cachedCompileBundleUsingProvider($bundleProvider, array $bundleProviderArguments = array())
	{
		return self::compileBundleUsingProvider($bundleProvider, $bundleProviderArguments);
	}

	/**
	 * @param string $bundleProvider
	 * @param array $bundleProviderArguments
	 *
	 * @return Compiler\Result
	 */
	private static function compileBundleUsingProvider($bundleProvider, array $bundleProviderArguments = array())
	{
		return self::compile(call_user_func_array(explode('::', $bundleProvider), $bundleProviderArguments));
	}
	/**
	 * @param AbstractBundle $bundle
	 *
	 * @return Compiler\Result
	 */
	public static function compile(AbstractBundle $bundle)
	{
		$dependencies = Dependency\Manager::getSortedDependencies($bundle);
		$dependencies[] = new Dependency($bundle, Dependency::TYPE_LINK);
		foreach ($dependencies as $dependency) {
			//Timer::start($timerAction = 'compile ' . $dependency->bundle->name);
			$dependency->compile();
			//Timer::stop($timerAction);
		}
		$topLevel = end($dependencies);
		self::build($topLevel, $bundle->debug);

		// if something has to be merged, do it now
		for ($i = 0; $i < count($topLevel->result->jsFiles); $i++) {
			$jsFiles = $topLevel->result->jsFiles[$i];
			if (is_array($jsFiles)) {
				$name = 'merge-' . md5(implode('-', $jsFiles));
				$basename =  $name . '.min.js';
				$filename = \Foomo\JS\Module::getHtdocsVarDir() . DIRECTORY_SEPARATOR . $basename;
				if (!file_exists($filename)) {
					$jsCompiler = JS::create($jsFiles)
						->name($name)
						->compress()
						->compile()
					;
					//rename($jsCompiler->getOutputFilename(), $filename);
					$oldContents = '';
					if(file_exists($filename)) {
						$oldContents = file_get_contents($filename);
					}
					$newContents = file_get_contents($jsCompiler->getOutputFilename());
					if($oldContents != $newContents) {
						file_put_contents($filename, $newContents);
					}
				}
				$topLevel->result->jsFiles[$i] = $filename;
				$topLevel->result->jsLinks[$i] = \Foomo\JS\Module::getHtdocsVarBuildPath($basename);
			} else {
				$topLevel->result->jsLinks[$i] = $topLevel->result->jsLinks[$i];//$jsFiles;//\Foomo\JS\Module::getHtdocsVarBuildPath(basename($jsFiles));
			}
		}
		return $topLevel->result;
	}

	public static function build(Dependency $dependency, $debug)
	{
		foreach ($dependency->bundle->dependencies as $parentDependency) {
			self::build($parentDependency, $debug);
			if ($parentDependency->type == Dependency::TYPE_MERGE && !$debug) {
				$merged = self::flattenArray($parentDependency->result->jsFiles);
				array_pop($dependency->result->jsLinks);                // remove the link as well
				$lastItem = array_pop($dependency->result->jsFiles);
				if (is_array($lastItem)) {
					$lastJs = array_pop($lastItem);
					$merged = array_merge($merged, $lastItem);
					$merged[] = $lastJs;
				} else {
					$merged[] = $lastItem;
				}
				$dependency->result->jsFiles[] = $merged;
			} else {
				// link
				$dependency->result->jsFiles = array_merge($parentDependency->result->jsFiles, $dependency->result->jsFiles);
				$dependency->result->jsLinks = array_merge($parentDependency->result->jsLinks, $dependency->result->jsLinks);
			}
		}
	}

	/**
	 * @param mixed[] $a array of arrays
	 * @return string[]
	 */
	private static function flattenArray($a)
	{
		$res = array();
		foreach ($a as $item) {
			if (is_array($item)) {
				$res = array_merge($res, self::flattenArray($item));
			} else {
				$res[] = $item;
			}
		}
		return $res;
	}
}