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
	private $filename;
	/**
	 * @var boolean
	 */
	private $watch = false;
	/**
	 * @var boolean
	 */
	private $compress = false;
	/**
	 * @var string
	 */
	private $name;
	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $filename
	 */
	public function __construct($filename)
	{
		$this->filename = $filename;
		if (!\file_exists($this->filename)) \trigger_error ('Source does not exist: ' . $this->filename, \E_USER_ERROR);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------
	/**
	 * @param string $name
	 * @return $this
	 */
	public function name($name)
	{
		$this->name = $name;
		return $this;
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
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @return string
	 */
	public function getOutputPath()
	{
		return \Foomo\JS\Module::getHtdocsVarBuildPath() . '/' . $this->getOutputBasename();
	}

	/**
	 * @return string
	 */
	public function getSourceFilename()
	{
		return $this->filename;
	}

	/**
	 * @return string
	 */
	public function getOutputFilename()
	{
		return \Foomo\JS\Module::getHtdocsVarDir() . DIRECTORY_SEPARATOR . $this->getOutputBasename();
	}

	/**
	 * @return string
	 */
	public function getOutputBasename()
	{
		$basename = \md5($this->filename);
		if ($this->compress) $basename .= '.min';
		$basename .= '.js';
		if(empty($this->name)) {
			return  $basename ;
		} else {
			return $this->name . '-' . $basename;
		}
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
	private function needsCompilation()
	{
		$source = $this->getFilename();
		$output = $this->getOutputFilename();

		$compile = (!\file_exists($output));

		if (!$compile && $this->getWatch()) {
			$deps = \Foomo\JS\Utils::getDependencies($source);
			$cmd = \Foomo\CliCall\Find::create($deps)->type('f')->newer($output)->execute();
			if (!empty($cmd->stdOut)) $compile = true;
		}
		return $compile;
	}
	/**
	 * @return \Foomo\JS
	 */
	public function compile()
	{
		$output = $this->getOutputFilename();
		if (
			$this->needsCompilation() &&
			Lock::lock($lockName = 'jsCompile-'. basename($output)) &&
			$this->needsCompilation()
		) {
			$source = $this->getFilename();
			$success = \Foomo\JS\Utils::compile($source, $output);
			if ($success && $this->compress) \Foomo\JS\Utils::uglify($output, $output);
			Lock::release($lockName);
		}

		return $this;
	}

	//---------------------------------------------------------------------------------------------
	// Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $filename Path to the js file
	 * @return \Foomo\JS
	 */
	public static function create($filename)
	{
		return new self($filename);
	}
}