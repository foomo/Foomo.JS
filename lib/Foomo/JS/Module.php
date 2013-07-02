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
	const VERSION	= '1.1.0';

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
			\Foomo\Modules\Resource\Module::getResource('Foomo', '0.3.0'),
			\Foomo\Modules\Resource\CliCommand::getResource('uglifyjs'),
		);
	}
	public static function make($target, MakeResult $result)
	{
		switch($target) {
			case 'clean':
				$result->addEntry('cleaning js files in ' . self::getHtdocsVarDir());
				foreach(new \DirectoryIterator(self::getHtdocsVarDir()) as $fileInfo) {
					if($fileInfo->isFile() && substr($fileInfo->getFilename(), -3) == '.js') {
						if(unlink($fileInfo->getPathname())) {
							$result->addEntry('removed ' . $fileInfo->getFilename());
						} else {
							$result->addEntry('could not remove ' . $fileInfo->getFilename(), MakeResult\Entry::LEVEL_ERROR, false);
						}
					}
				}
				break;
			default:
				parent::make($target, $result);
		}
	}

}