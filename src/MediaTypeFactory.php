<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType;

use NoreSources\Bitset;
use NoreSources\SingletonTrait;
use NoreSources\Container\Container;

/**
 * Media Type and Media Range factory
 *
 * Constructs Media Type and Media Range from various ways.
 */
class MediaTypeFactory
{
	use SingletonTrait;

	/**
	 * Parse a media type string
	 *
	 * @param string $mediaTypeString
	 *        	Medig type string
	 * @param boolean $acceptRange
	 *        	Accept Media ranges
	 * @throws MediaTypeException
	 * @return \NoreSources\MediaType\MediaType \NoreSources\MediaType\MediaRange
	 */
	public static function createFromString($mediaTypeString,
		$acceptRange = true)
	{
		try
		{
			return MediaType::createFromString($mediaTypeString, true);
		}
		catch (MediaTypeException $e)
		{
			if (!$acceptRange)
				throw $e;
			return MediaRange::createFromString($mediaTypeString, true);
		}
	}

	/**
	 * Attempt to guess media type from media content
	 *
	 * @var integer
	 */
	const FROM_CONTENT = Bitset::BIT_01;

	/**
	 * ttempt to guess media type from file extension
	 *
	 * @var integer
	 */
	const FROM_EXTENSION = Bitset::BIT_02;

	/**
	 * ttempt to guess media type from file extension first
	 *
	 * @var integer
	 */
	const FROM_ALL = self::FROM_CONTENT | self::FROM_EXTENSION;

	const FROM_EXTENSION_FIRST = self::FROM_EXTENSION | Bitset::BIT_03;

	/**
	 * Get media type of a file or stream
	 *
	 * @param string|resource $media
	 *        	File path or stream
	 * @param integer $mode
	 *        	Media type guessing options
	 * @return \NoreSources\MediaType\MediaType
	 */
	public static function createFromMedia($media,
		$mode = self::FROM_ALL)
	{
		$contentType = null;
		$extensionType = null;
		$type = null;

		if (($mode & self::FROM_EXTENSION) == self::FROM_EXTENSION &&
			\is_string($media) &&
			($extension = pathinfo($media, PATHINFO_EXTENSION)))
		{
			$extensionRegistry = MediaTypeFileExtensionRegistry::getInstance();
			$extensionType = $extensionRegistry->getExtensionMediaType(
				$extension);

			if ($extensionType instanceof MediaTypeInterface)
				if (($mode & self::FROM_EXTENSION_FIRST) ==
					self::FROM_EXTENSION_FIRST)
					return $extensionType;
		}

		if (($mode & self::FROM_CONTENT) == self::FROM_CONTENT)
		{
			if (\is_string($media) && \is_file($media))
			{
				if (\class_exists('\finfo'))
				{
					$finfo = new \finfo(FILEINFO_MIME_TYPE);
					$contentType = $finfo->file($media);
				}
				elseif (\function_exists('\mime_content_type'))
				{
					$contentType = @mime_content_type($media);
				}
			}
			elseif (\is_resource($media) &&
				\function_exists('\get_resource_type'))
			{
				$type = \get_resource_type($media);
				if ($type == 'stream' &&
					\function_exists('\stream_get_meta_data') &&
					($meta = \stream_get_meta_data($media)))
				{
					$contentType = Container::keyValue($meta,
						'mediatype', null);
				}
			}
		}

		$p = '^' . RFC6838::RESTRICTED_NAME_PATTERN . '/x-empty$';

		if ($extensionType &&
			(empty($contentType) ||
			(\strcasecmp('text/plain', $contentType) == 0) ||
			preg_match(chr(1) . $p . chr(1) . 'i', $contentType)))
			return $extensionType;

		if (\is_string($contentType))
			return MediaType::createFromString($contentType);

		throw new MediaTypeException(null,
			'Unable to recognize media type');
	}
}
