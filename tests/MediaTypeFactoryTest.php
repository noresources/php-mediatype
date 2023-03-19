<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType\Test;

use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeFactoryAwareInterface;
use NoreSources\MediaType\Traits\MediaTypeFactoryAwareTrait;

class Aware implements MediaTypeFactoryAwareInterface
{
	use MediaTypeFactoryAwareTrait;
}

class MediaTypeFactoryTest extends \PHPUnit\Framework\TestCase
{

	function testAware()
	{
		$dflt = MediaTypeFactory::getInstance();
		$aware = new Aware();
		$this->assertEquals(NULL, $aware->getMediaTypeFactory(),
			'Aware factory is NULL by default');
		$aware->setMediaTypeFactory($dflt);
		$this->assertEquals($dflt, $aware->getMediaTypeFactory(),
			'Aware factory set');
	}
}
