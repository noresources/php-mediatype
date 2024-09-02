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
	StringRepresentation, \JsonSerializable, \Serializable
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

	const STRUCTURED_TEXT_ONLY_REGISTERED = 0x01;

	/**
	 *
	 * See RFC 6838 Section 3
	 *
	 * @var number
	 */
	const STRUCTURED_TEXT_BYPASS_KNOWN_TREE_FACET = 0x02;

	const STRUCTURED_TEXT_REMOVE_LEGACY_UNREGISTERED_PREFIX = 0x04;

	/**
	 * Get the subtype structured syntax name if any.
	 *
	 * @param integer|boolean $toleranceFlags
	 *        	Tolerance flags.
	 *        	For backward compatibility, true is interpreted as
	 *        	STRUCTURED_TEXT_ONLY_REGISTERED.Y
	 *
	 * @return string|array|string|NULL
	 */
	function getStructuredSyntax($toleranceFlags = 0);
}