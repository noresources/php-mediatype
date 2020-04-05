# ns-php-mediatype

RFC 6838 Media Type (MIME type) parsing and comparison.  

## Installation

```bash
composer require noresources/ns-php-mediatype ~1.0
```

## Usage
```php

use NoreSources\MediaType\MediaType;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaRange;

$mediaType = MediaTypeFactory::fromString ('text/vnd.noresources.incredibly.flexible+xml');

var_dump ($mediaType->getMainType()); i       // "text"
var_dump ($mediaType->getStructuredSyntax()); // "xml"

$subType = $mediaType->getSubType ();
var_dump (\strval ($subType));                // "vnd.noresources.incredibly.flexible+xml"
var_dump ($subType->getFacets());             // [ "vnd", "noresources", "incredibly", "flexible" ]


// From a file or a stream
$mediaType = MediaTypeFactory::fromMedia ('path/to/filename.html');
var_dump (\strval ($mediaType)); // "text/html"

// Media range is also recognized
$range = MediaTypeFactory::fromString ('image/*');

// Comparing
$html = MediaTypeFactory::fromString ('text/html');
$anyText = MediaTypeFactory::fromString ('text/*');
$any = MediaTypeFactory::fromString ('*/*');

var_dump ([
	'text/html vs text/*' => MediaRange::compare ($html, $anyText),
	'text/* vs */*' => MediaRange::compare ($anyText, $any),
	'*/* vs text/html' => MediaRange::compare ($any, $html)
]);

/* 
array(3) {
  ["text/html vs text/*"]=> int(1)
  ["text/* vs */*"]=> int(1)
  ["*/* vs text/html"]=> int(-1)
}
*/
```

## References

- [RFC 6838 Media Type Specifications and Registration Procedures](https://tools.ietf.org/html/rfc6838)
 - [RFC 4288](https://tools.ietf.org/html/rfc4288#section-4.3)
- [RFC 7231 Hypertext Transfer Protocol (HTTP/1.1): Semantics and Content](https://tools.ietf.org/html/rfc7231#section-3.1.1.1)
- [IANA Media Type registration list](https://www.iana.org/assignments/media-types/media-types.xhtml)
- [Apache Media Type file extensions associations](https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types)
