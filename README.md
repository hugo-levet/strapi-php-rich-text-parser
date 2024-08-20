# strapi-php-rich-text-parser

A PHP parser for Strapi Rich Text fields

## Usage

Get datas from strapi api in StdClass format and pass it to the parser

```php
use HugoLevet\StrapiPhpRichTextParser\RichTextParser;

$html_content = RichTextParser::jsonToHtml($content);
```

## Environment variables

Make sure to set the environment variable `STRAPI_URL` with the URL of the Strapi API you are using
