# strapi-php-rich-text-parser

[![Latest Version](https://img.shields.io/packagist/v/hugo-levet/strapi-php-rich-text-parser.svg?style=flat-square)](https://packagist.org/packages/hugo-levet/strapi-php-rich-text-parser)
[![Total Downloads](https://img.shields.io/packagist/dt/hugo-levet/strapi-php-rich-text-parser.svg?style=flat-square)](https://packagist.org/packages/hugo-levet/strapi-php-rich-text-parser)
[![Software License](https://img.shields.io/badge/License-MIT-brightgreen.svg?style=flat-square)](LICENSE)

A PHP parser for Strapi Rich Text fields

## Usage

Get datas from strapi api in StdClass format and pass it to the parser

```php
use HugoLevet\StrapiPhpRichTextParser\RichTextParser;

$html_content = RichTextParser::jsonToHtml($content);
```

## Environment variables

Make sure to set the environment variable `STRAPI_URL` with the URL of the Strapi API you are using
