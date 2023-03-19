<?php
namespace NoreSources\MediaType\Traits;

use NoreSources\MediaType\MediaTypeFactory;

/**
 * Default impmementation of MediaTypeFactoryAwareInterface
 */
trait MediaTypeFactoryAwareTrait
{

	public function setMediaTypeFactory(MediaTypeFactory $factory)
	{
		$this->mediaTypeFactory = $factory;
	}

	public function getMediaTypeFactory()
	{
		return $this->mediaTypeFactory;
	}

	/**
	 *
	 * @var MediaTypeFactory
	 */
	private $mediaTypeFactory;
}
