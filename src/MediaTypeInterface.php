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

use NoreSources\StringRepresentation;
use NoreSources\ModifiableKeyValueParameterMapInterface;
use NoreSources\Http\ParameterMapProviderInterface;

interface MediaTypeInterface extends ParameterMapProviderInterface, StringRepresentation
{

	/**
	 *
	 * @return string
	 */
	function getType();

	/**
	 *
	 * @return \NoreSources\MediaType\MediaSubType|string
	 */
	function getSubType();

	/**
	 * Get the subtype structured syntax name if any.
	 *
	 * @param boolean $registeredOnly
	 *        	When the structured syntax suffix is not present. The subtype may be returned.
	 *        	If $registeredOnly is true. Only the subtype will be returned
	 *        	only if it correspond to a registered suffix.
	 *
	 * @return string|array|string|NULL
	 */
	function getStructuredSyntax($registeredOnly = false);
}