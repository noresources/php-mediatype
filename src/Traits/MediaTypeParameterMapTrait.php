<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType\Traits;

use NoreSources\Http\ParameterMap;
use NoreSources\Http\ParameterMapProviderTrait;

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