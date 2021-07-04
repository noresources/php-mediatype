<?php
namespace NoreSources\MediaType;

require (__DIR__ . '/../vendor/autoload.php');

use NoreSources\Container\Container;
use NoreSources\Type\TypeConversion;
$url = 'https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
$filename = __DIR__ . '/../resources/httpd/mime.types';

$outputFileBase = __DIR__ . '/../src/MediaTypeFileExtensionRegistry';

/*
 * Download Apache httpd public registry
 */
if (\in_array('--download', $_SERVER['argv'])) // 403
{
	$tmp = $filename . '.download';
	$file = fopen($tmp, 'w');
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_FILE, $file);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_USERAGENT,
		"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
	$result = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	fclose($file);
	if ($result && $status == 200)
		rename($tmp, $filename);
	else
	{
		unlink($tmp);
		\trigger_error(
			'Failed to download resource file (result: ' .
			TypeConversion::toString($result) . ', status: ' . $status .
			')');
		exit(1);
	}
}

/*
 * Update source code
 */

$content = file_get_contents($filename);
$lines = explode("\n", $content);

$match = [];
$typeMap = [];
$extensionMap = [];
foreach ($lines as $index => $line)
{

	if (!\preg_match(
		chr(1) . '^(?:#\s+)?(' . RFC6838::MEDIA_TYPE_PATTERN .
		')\s+([a-zA-Z0-9. ]+)' . chr(1), $line, $match))
	{
		continue;
	}

	$mediaType = MediaType::fromString($match[1]);
	$extensions = \preg_split('/\s+/', $match[4]);

	echo (sprintf('%-32.32s %s', \strval($mediaType),
		\implode(', ', $extensions)) . PHP_EOL);

	foreach ($extensions as $extension)
	{
		$extensionMap[$extension] = \strval($mediaType);
	}

	if (!Container::keyExists($typeMap, $mediaType->getType()))
	{
		$typeMap[$mediaType->getType()] = [];
	}

	$typeMap[$mediaType->getType()][\strval($mediaType->getSubType())] = $extensions;
}

file_put_contents($outputFileBase . '/extensions.json',
	\json_encode($extensionMap));
foreach ($typeMap as $main => $sub)
{
	file_put_contents($outputFileBase . '/types.' . $main . '.json',
		\json_encode($sub));
}


