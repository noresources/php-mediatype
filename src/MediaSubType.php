<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType;

use NoreSources\ComparableInterface;
use NoreSources\NotComparableException;
use NoreSources\Container\Container;
use NoreSources\Type\StringRepresentation;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeDescription;

/**
 * Media sub type
 */
class MediaSubType implements StringRepresentation, ComparableInterface
{

	/**
	 *
	 * @param array|string $facets
	 * @param string|null $structuredSyntax
	 */
	public function __construct($facets, $structuredSyntax = null)
	{
		$this->facets = $facets;
		$this->structuredSyntax = $structuredSyntax;

		if (\is_string($facets))
			$this->facets = explode('.', $facets);
	}

	/**
	 * String representation of the subtype and structured syntax type if any
	 *
	 * @return string
	 */
	#[\ReturnTypeWillChange]
	public function __toString()
	{
		$s = \implode('.', $this->facets);
		if (\is_string($this->structuredSyntax) &&
			\strlen($this->structuredSyntax))
			$s .= '+' . $this->structuredSyntax;

		return $s;
	}

	/**
	 *
	 * @return array Subtype facets
	 */
	public function getFacets()
	{
		return $this->facets;
	}

	/**
	 *
	 * @param integer $index
	 * @return string|NULL Subtype facet at the given index or null if the index does not exists
	 */
	public function getFacet($index)
	{
		return Container::keyValue($this->facets, $index, null);
	}

	/**
	 *
	 * @return integer Number of sub type facets
	 */
	public function getFacetCount()
	{
		return count($this->facets);
	}

	/**
	 * Get the sub type structured syntax name
	 *
	 * @see https://tools.ietf.org/html/rfc6838#section-4.2.8
	 * @return string If any, the lower-case structured syntax name
	 */
	public function getStructuredSyntax()
	{
		if (!empty($this->structuredSyntax))
			return strtolower($this->structuredSyntax);
		return null;
	}

	/**
	 * Compare sub type precision with another
	 *
	 * A sub type {a} is more precise than another sub type {b} if
	 * - {a} has at least one facet and {b} is empty
	 * - All facets of {b} are identical to the first facets of {a} and {a} has more facets than {b}
	 * -
	 * A NotComparableException is thrown if a facet of {a}
	 * does not match the facet of {b} at the same position.
	 *
	 * @param MediaSubType|MediaTypeInterface|string $b
	 *        	Media sub type to compare with
	 * @throws NotComparableException
	 * @return 0 if sub types are identical,
	 *         < 0 if $this is less precise than $b,
	 *         and > 0 if $this is more precise than $b
	 */
	public function compare($b)
	{
		$a = $this;
		if ($b instanceof MediaTypeInterface)
			$b = $b->getSubType();

		if (!($b instanceof MediaSubType))
		{
			if (!TypeDescription::hasStringRepresentation($b))
				throw new NotComparableException($a, $b);

			$m = MediaRange::createFromString(
				MediaRange::ANY . '/' . TypeConversion::toString($b));
			$b = $m->getSubType();
		}

		$fca = $a->getFacetCount();
		$fcb = $b->getFacetCount();
		$fcm = min($fca, $fcb);
		for ($i = 0; $i < $fcm; $i++)
		{

			$fa = $a->getFacet($i);
			$fb = $b->getFacet($i);

			if (\strcasecmp($fa, $fb) !== 0)
				throw new NotComparableException($fa, $fb);
		}

		return ($fca > $fcb) ? 1 : (($fca < $fcb) ? -1 : 0);
	}

	/**
	 *
	 * @var array
	 */
	private $facets;

	/**
	 *
	 * @var string
	 */
	private $structuredSyntax;
}

