<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\MediaType;

use NoreSources\ComparableInterface;
use NoreSources\StringRepresentation;
use NoreSources\Http\ParameterMapProviderInterface;

/**
 * Media Type / Media Range interface
 *
 * <ul>
 * <li>String representation must return the Media Type/Range without parameters</li>
 * <li>Serialized form MUST return the media type / range followed by a semicolon-separated list of
 * parameters</li>
 *
 * media-type = type "/" sub-type
 *
 * serialized = media-type 0*(";" parameter)
 *
 * parameter = token "=" ( token / quoted )
 * </ul>
 */
interface MediaTypeInterface extends ParameterMapProviderInterface, StringRepresentation,
	\Serializable, ComparableInterface
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