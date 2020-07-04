<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources\MediaType;

use NoreSources\Http\ParameterMapProviderTrait;
use NoreSources\Http\ParameterMap;

trait MediaTypeParameterMapTrait
{
	use ParameterMapProviderTrait;

	protected function setParameters($parameters)
	{
		$this->parameters = new ParameterMap();
		foreach ($parameters as $key => $value)
			$this->parameters->offsetSet($key, $value);
	}
}