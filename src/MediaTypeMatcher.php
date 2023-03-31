<?php
namespace NoreSources\MediaType;

class MediaTypeMatcher
{

	/**
	 *
	 * @param MediaTypeInterface $mediaType
	 *        	Media type to match agains list of media types/ranges
	 */
	public function __construct(MediaTypeInterface $mediaType)
	{
		$this->mediaType = $mediaType;
	}

	/**
	 *
	 * @param MediaTypeInterface[] $list
	 *        	List of media types/ranges
	 * @return MediaTypeInterface[]
	 */
	public function getMatching($list)
	{
		$matches = [];
		foreach ($list as $e)
		{
			if ($this->mediaType->match($e))
				$matches[] = $e;
		}
		return $matches;
	}

	/**
	 *
	 * @param MediaTypeInterface $list
	 *        	List of media types/ranges
	 * @return MediaTypeInterface|boolean
	 */
	public function getFirstMatching($list)
	{
		foreach ($list as $e)
		{
			if ($this->mediaType->match($e))
				return $e;
		}
		return false;
	}

	/**
	 *
	 * @param MediaTypeInterface[] $list
	 *        	List of media types/ranges
	 * @return boolean TRUE if at least one element of $list match the media type
	 */
	public function match($list)
	{
		return $this->getFirstMatching($list) !== false;
	}

	public function __invoke($list)
	{
		return $this->match($list);
	}

	/**
	 *
	 * @var MediaTypeInterface
	 */
	private $mediaType;
}
