<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType;

use NoreSources\Http\ParameterMapProviderInterface;
use NoreSources\Type\StringRepresentation;

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
interface MediaTypeInterface extends ParameterMapProviderInterface,
	StringRepresentation, \JsonSerializable
{

	/**
	 * Wildcard type or subtype
	 *
	 * @var string
	 */
	const ANY = '*';

	/**
	 * Check if the MediaType instance match the given MediaRange
	 *
	 * @param MediaTypeInterface|string $mediaRange
	 * @return true if $mediaRange is identical or less restrictive than $this
	 */
	public function match($mediaRange);

	/**
	 * Get media type main type
	 *
	 * Any RFC 6838 restricted name token or the wildcard token "*"
	 *
	 * @return string
	 */
	function getType();

	/**
	 * Get media type sub type and optional structured syntax suffix
	 *
	 * Any of the following
	 * <ul>
	 * <li>A RFC 6838 restricted name token, optionnaly followed by a "+" and a structured syntax
	 * suffix</li>
	 * <li>The wildcard token.</li>
	 * </ul>
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