<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType\Traits;

use NoreSources\Http\ParameterMapSerializer;

trait MediaTypeSerializableTrait
{

	/**
	 *
	 * @return string String representation of the media type and its optional parameters
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->serialize();
	}

	/**
	 *
	 * @return string Media Type / Range and parameters
	 *
	 * @deprecated Use jsonSerialize()
	 */
	public function serialize()
	{
		$s = \strval($this);
		if ($this->getParameters()->count())
			$s .= '; ' .
				ParameterMapSerializer::serializeParameters(
					$this->getParameters());
		return $s;
	}

	/**
	 *
	 * @param string $serialized
	 *        	Media Type and parameterss
	 */
	public function unserialize($serialized)
	{
		$p = \strpos($serialized, ';');
		$mts = \trim(
			($p === false) ? $serialized : \substr($serialized, 0, $p));
		$mt = static::createFromString($mts);

		$this->mainType = $mt->getType();
		$this->subType = $mt->getSubType();

		if ($p !== false)
		{
			$parameters = [];
			ParameterMapSerializer::unserializeParameters($parameters,
				\trim(\substr($serialized, $p + 1)));
			$this->setParameters($parameters);
		}
	}
}