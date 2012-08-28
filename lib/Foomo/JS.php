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

namespace Foomo;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author franklin <franklin@weareinteractive.com>
 */
class JS
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var string
	 */
	private $module;
	/**
	 * @var string
	 */
	private $source;
	/**
	 * @var boolean
	 */
	private $watch = false;
	/**
	 * @var boolean
	 */
	private $compress = false;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $module
	 * @param string $source name of the js file
	 */
	public function __construct($module, $source)
	{
		$this->module = $module;
		$this->source = (substr($source, -3) != '.js') ? $source . '.js' : $source;
		if (!\file_exists($this->getSourceFilename())) \trigger_error ('Source does not exist: ' . $this->getSourceFilename (), \E_USER_ERROR);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * @return boolean
	 */
	public function getWatch()
	{
		return $this->watch;
	}

	/**
	 * @return boolean
	 */
	public function getCompress()
	{
		return $this->compress;
	}

	/**
	 * @return string
	 */
	public function getOutputPath()
	{
		return \Foomo\JS\Module::getHtdocsVarPath() . DIRECTORY_SEPARATOR . $this->getOutputBasename();
	}

	/**
	 * @return string
	 */
	public function getSourceFilename()
	{
		return \Foomo\Config::getModuleDir($this->module) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . $this->source;
	}

	/**
	 * @param boolean $pattern
	 * @return string
	 */
	public function getOutputFilename($pattern=false)
	{
		return \Foomo\JS\Module::getHtdocsVarDir() . DIRECTORY_SEPARATOR . $this->getOutputBasename($pattern);
	}

	/**
	 * @return string
	 */
	public function getSourceBasename()
	{
		return $this->source;
	}

	/**
	 * @param boolean $pattern
	 * @return string
	 */
	public function getOutputBasename($pattern=false)
	{
		$basename = $this->module . '-' . $this->source;
		if ($this->compress) $basename .= '.min';
		return  $basename . '.js';
	}

	/**
	 * @param boolean $watch
	 * @return \Foomo\JS
	 */
	public function watch($watch=true)
	{
		$this->watch = $watch;
		return $this;
	}

	/**
	 * @param boolean $compress
	 * @return \Foomo\JS
	 */
	public function compress($compress=true)
	{
		$this->compress = $compress;
		return $this;
	}

	/**
	 * @return \Foomo\JS
	 */
	public function compile()
	{
		$source = $this->getSourceFilename();
		$output = $this->getOutputFilename();

		$compile = (!\file_exists($output));

		if (!$compile && $this->getWatch()) {
			$deps = \Foomo\JS\Utils::getDependencies($source);
			$cmd = \Foomo\CliCall\Find::create($deps)->type('f')->newer($output)->execute();
			if (!empty($cmd->stdOut)) $compile = true;
		}

		if ($compile) {
			$success = \Foomo\JS\Utils::compile($source, $output);
			if ($success && $this->compress) \Foomo\JS\Utils::uglify($output, $output);
		}

		return $this;
	}

	//---------------------------------------------------------------------------------------------
	// Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $module
	 * @param string $name name of the js file
	 * @return \Foomo\JS
	 */
	public static function create($module, $name)
	{
		return new self($module, $name);
	}
}