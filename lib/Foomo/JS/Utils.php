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

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar <jan@bestbytes.com>
 * @author franklin <franklin@weareinteractive.com>
 */
class Utils
{
	//---------------------------------------------------------------------------------------------
	// Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $source
	 * @param string $destination
	 * @return boolean
	 */
	public static function compile($source, $destination)
	{
		$pwd = \getcwd();

		# cd to the modules dir
		\chdir(\Foomo\Config::getModuleDir());

		$script = self::concatImports($source);

		\file_put_contents($destination, $script);

		\chdir($pwd);
		return true;
	}

	/**
	 * @param string $source
	 * @param string $destination
	 * @return boolean
	 */
	public static function uglify($source, $destination)
	{
		# uglify
		$cmd = \Foomo\CliCall\Uglifyjs::create($source)->execute();
		# validate
		if ($cmd->exitStatus !== 0) {
			\trigger_error('uglifying failed:' . $cmd->stdErr, E_USER_WARNING);
			return false;
		} else {
			\file_put_contents($destination, $cmd->stdOut);
			return true;
		}
	}

	/**
	 * @param string $filename
	 * @return string[]
	 */
	public static function getDependencies($filename)
	{
		$deps = array($filename);
		self::crawlDependencies($filename, $deps);
		return $deps;
	}

	//---------------------------------------------------------------------------------------------
	// Private static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $filename
	 * @param string[] $deps
	 */
	private static function crawlDependencies($filename, array &$deps)
	{
		foreach (self::extractImports($filename) as $import) {
			if (!in_array($import, $deps)) {
				$deps[] = $import;
				self::crawlDependencies($import, $deps);
			}
		}
	}

	/**
	 * Extract @import("....js");
	 *
	 * @param type $filename
	 * @return string[] absolute filename
	 */
	private static function extractImports($filename)
	{
		$imports = array();
		$matches = self::pregMatch(\file_get_contents($filename));

		foreach ($matches[1] as $rawImport) {
			$resolvedFilename = self::resolveFilename($filename, $rawImport);
			if(!empty($resolvedFilename)) {
				$imports[] = $resolvedFilename;
			} else {
				trigger_error('can not resolve import in ' . $filename . ' to ' . $rawImport);
			}
		}

		return $imports;
	}

	/**
	 * @param string $string
	 * @return array
	 */
	private static function pregMatch($string)
	{
		$matches = array();
		//\preg_match_all('/^@include\(["\'](.*?)["\']\)/im', $string, $matches);
		//return $matches;
		\preg_match_all('/^\s*@include\s*\(\s*(["\'])(.*?)\1\s*\)/im', $string, $matches);
		return array($matches[0], $matches[2]);
	}

	/**
	 * Resolve a filename relative to a js filename
	 *
	 * @param string $filename absolute filename of the js file
	 * @param string $path (relative) filename of the import
	 * @return string resolved absolute filename
	 */
	private static function resolveFilename($filename, $path)
	{
		if (substr($path, 0, 2) == './') $path = substr($path, 2);
		foreach (array('', '.js') as $suffix) {
			foreach (self::getLookupRoots($filename) as $rootDir) {
				$resolvedFilename = $rootDir . $path . $suffix;
				if (file_exists($resolvedFilename) && is_file($resolvedFilename)) {
					return $resolvedFilename;
				}
			}
		}
	}

	/**
	 * Get import lookup root directories for a js file
	 *
	 * @param string $filename absolute filename of the .js file
	 * @return string[]
	 */
	private static function getLookupRoots($filename)
	{
		return array(
			'', // absolute path given
			\dirname($filename) . DIRECTORY_SEPARATOR, // relative path
			\Foomo\Config::getModuleDir() . DIRECTORY_SEPARATOR // modules dir
		);
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	private static function concatImports($filename)
	{
		$script = \file_get_contents($filename);
		$matches = self::pregMatch($script);

		for ($i=0; $i<count($matches[0]); $i++) {
			$importFilename = self::resolveFilename($filename, $matches[1][$i]);
			if(is_null($importFilename)) {
				trigger_error('invalid import : ' . $matches[1][$i] . ' in ' . $filename, E_USER_WARNING);
			} else {
				$importScript = self::concatImports($importFilename);
				$script = \str_replace($matches[0][$i], $importScript, $script);
			}
		}

		return $script;
	}
}
