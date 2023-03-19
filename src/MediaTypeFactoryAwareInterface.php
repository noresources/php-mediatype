<?php
namespace NoreSources\MediaType;

interface MediaTypeFactoryAwareInterface
{

	/**
	 * Define the instance of media type factory to use for this object
	 *
	 * @param MediaTypeFactory $factory
	 *        	Media type factory
	 */
	function setMediaTypeFactory(MediaTypeFactory $factory);

	/**
	 *
	 * @return MediaTypeFactory|NULL
	 */
	function getMediaTypeFactory();
}
