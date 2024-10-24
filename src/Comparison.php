<?php
namespace NoreSources\MediaType;

use NoreSources\NotComparableException;
use NoreSources\Container\Container;

class Comparison
{

	/**
	 * COmpare two media type or media range by their precision level
	 *
	 * @param MediaTypeInterface $a
	 *        	First operand
	 * @param MediaTypeInterface $b
	 *        	Second operand
	 * @throws NotComparableException
	 * @return integer
	 */
	public static function rangePrecision(MediaTypeInterface $a,
		MediaTypeInterface $b)
	{
		if ($a->getType() == MediaRange::ANY)
			return (($b->getType() == MediaRange::ANY) ? 0 : -1);
		elseif ($b->getType() == MediaRange::ANY)
			return 1;

		if (\strcasecmp($a->getType(), $b->getType()) !== 0)
			throw new NotComparableException($a->getType(),
				$b->getType());

		if ($a->getSubType() == MediaRange::ANY)
			return (($b->getSubType() == MediaRange::ANY) ? 0 : -1);
		elseif ($b->getSubType() == MediaRange::ANY)
			return 1;

		return $a->getSubType()->precisionCompare($b->getSubType());
	}

	public static function lexical(MediaTypeInterface $a,
		MediaTypeInterface $b)
	{
		$pf = MediaTypeInterface::PART_TYPE;
		$c = \strcasecmp($a->toString($pf), $b->__toString($pf));
		if ($c != 0)
			return $c;
		$pa = $a->getParameters();
		$pb = $b->getParameters();
		$ka = Container::keys($pa);
		\sort($ka);
		$kb = Container::keys($pb);
		\sort($kb);
		$c = \strcasecmp(\implode('=', $ka), \implode('=', $kb));
		if ($c != 0)
			return $c;
		foreach ($ka as $key)
		{
			$va = $pa[$key];
			$vb = $pb[$key];
			$c = \strcasecmp($va, $vb);
			if ($c != 0)
				return $c;
		}
		return 0;
	}
}
