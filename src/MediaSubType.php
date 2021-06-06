<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
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

	public function compare($b)
	{
		if ($b instanceof MediaTypeInterface)
			$b = $b->getSubType();

		if (!($b instanceof MediaSubType))
		{
			if (!TypeDescription::hasStringRepresentation($b))
				throw new NotComparableException($this, $b);

			$m = MediaRange::fromString(
				MediaRange::ANY . '/' . TypeConversion::toString($b));
			$b = $m->getSubType();
		}

		$fca = $this->getFacetCount();
		$fcb = $b->getFacetCount();
		$fcm = min($fca, $fcb);
		$fcM = max($fca, $fcb);
		$i = 0;
		for (; $i < $fcm; $i++)
		{
			$fa = $this->getFacet($i);
			$fb = $b->getFacet($i);

			if ($fb != $fb)
			{
				if ($i == ($fcM - 1))
					return \strcmp($fa, $fb);

				return 0;
			}
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

