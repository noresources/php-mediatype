<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType;

/**
 * Constants defined in RFC 6838
 *
 * @see https://tools.ietf.org/html/rfc6838
 */
class RFC6838
{

	/**
	 * Media type and media sub type name pattern
	 *
	 * * Characters before first dot always specify a facet name
	 * * Characters after last plus always specify a structured syntax suffix
	 *
	 * @see https://tools.ietf.org/html/rfc6838#section-4.2
	 *
	 * @var string
	 */
	const RESTRICTED_NAME_PATTERN = '[A-Za-z0-9][A-Za-z0-9!#$&^_.+-]{0,126}';

	const RANGE_PATTERN = '\*';

	const OPTIONAL_NAME_PATTERN = '(?:' . self::RANGE_PATTERN . ')|(?:' .
		self::RESTRICTED_NAME_PATTERN . ')';

	/**
	 * Media type pattern with the following groups
	 * <ol>
	 * <li>Main type</li>
	 * * <li>Sub type facets and structured text syntax suffix</li>
	 * </ol>
	 */
	const MEDIA_TYPE_PATTERN = '(' . self::RESTRICTED_NAME_PATTERN .
		')/(' . self::RESTRICTED_NAME_PATTERN . ')';

	/**
	 * Media range pattern with the following groups
	 * <ol>
	 * <li>Main type or *</li>
	 * * <li>Sub type facets and structured text syntax suffix or *</li>
	 * </ol>
	 */
	const MEDIA_RANGE_PATTERN = '(' . self::OPTIONAL_NAME_PATTERN . ')/(' .
		self::OPTIONAL_NAME_PATTERN . ')';
}
