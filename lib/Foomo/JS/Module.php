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

namespace Foomo\JS;
use Foomo\Cache\Invalidator;
use Foomo\Modules\MakeResult;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class Module extends \Foomo\Modules\ModuleBase
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const NAME		= 'Foomo.JS';
	const VERSION	= '1.2.4';

	//---------------------------------------------------------------------------------------------
	// ~ Overriden static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * Get a plain text description of what this module does
	 *
	 * @return string
	 */
	public static function getDescription()
	{
		return 'Simple JS resource handling';
	}

	/**
	 * get all the module resources
	 *
	 * @return \Foomo\Modules\Resource[]
	 */
	public static function getResources()
	{
		return array(
			\Foomo\Modules\Resource\Module::getResource('Foomo', '0.3.*'),
			\Foomo\Modules\Resource\Module::getResource('Foomo.Bundle', '0.1.*'),
			//\Foomo\Modules\Resource\NPMPackage::getResource('uglify-js', '3.17.4'),
			\Foomo\Modules\Resource\CliCommand::getResource('uglifyjs')
		);
	}


	public static function make($target, MakeResult $result)
	{
		switch($target) {
			case 'clean':
				\Foomo\Bundle\Module::cleanDir(self::getHtdocsVarDir(), $result);
				\Foomo\Bundle\Module::cleanDir(self::getVarDir(), $result);
				break;
			default:
				parent::make($target, $result);
		}
	}
}