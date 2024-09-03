<?php

require 'src/RichTextParser.php';

use PHPUnit\Framework\TestCase;
use HugoLevet\StrapiPhpRichTextParser\RichTextParser;

final class CommonTest extends TestCase
{
    public function testHeading(): void
    {
        $this->assertEquals('<h1>Heading 1</h1>', RichTextParser::jsonToHtml(json_decode('[{"type":"heading","children":[{"type":"text","text":"Heading 1"}],"level": 1}]')));
    }

    public function testHeading2(): void
    {
        $this->assertEquals('<h2>Heading 2</h2>', RichTextParser::jsonToHtml(json_decode('[{"type":"heading","children":[{"type":"text","text":"Heading 2"}],"level":2}]')));
    }

    public function testParagraph(): void
    {
        $this->assertEquals('<p>Paragraph 1</p>', RichTextParser::jsonToHtml(json_decode('[{"type":"paragraph","children":[{"type":"text","text":"Paragraph 1"}]}]')));
    }

    public function testBold(): void
    {
        $this->assertEquals('<p><strong>Bold text</strong></p>', RichTextParser::jsonToHtml(json_decode('[{"type":"paragraph","children":[{"type":"text","text":"Bold text","bold":true}]}]')));
    }

    public function testItalic(): void
    {
        $this->assertEquals('<p><em>Italic text</em></p>', RichTextParser::jsonToHtml(json_decode('[{"type":"paragraph","children":[{"type":"text","text":"Italic text","italic":true}]}]')));
    }

    public function testUnderline(): void
    {
        $this->assertEquals('<p><u>Underline text</u></p>', RichTextParser::jsonToHtml(json_decode('[{"type":"paragraph","children":[{"type":"text","text":"Underline text","underline":true}]}]')));
    }

    public function testStrikethrough(): void
    {
        $this->assertEquals('<p><del>Strikethrough text</del></p>', RichTextParser::jsonToHtml(json_decode('[{"type":"paragraph","children":[{"type":"text","text":"Strikethrough text","strikethrough":true}]}]')));
    }

    public function testCode(): void
    {
        $this->assertEquals('<p><code>Code text</code></p>', RichTextParser::jsonToHtml(json_decode('[{"type":"paragraph","children":[{"type":"text","text":"Code text","code":true}]}]')));
    }

    public function testList(): void
    {
        $this->assertEquals('<ul><li>Item 1</li><li>Item 2</li></ul>', RichTextParser::jsonToHtml(json_decode('[{"type":"list","children":[{"type":"list-item","children":[{"type":"text","text":"Item 1"}]},{"type":"list-item","children":[{"type":"text","text":"Item 2"}]}],"format":"unordered"}]')));
    }

    public function testList2(): void
    {
        $this->assertEquals('<ol><li>Item 1</li><li>Item 2</li></ol>', RichTextParser::jsonToHtml(json_decode('[{"type":"list","children":[{"type":"list-item","children":[{"type":"text","text":"Item 1"}]},{"type":"list-item","children":[{"type":"text","text":"Item 2"}]}],"format":"ordered"}]')));
    }

    public function testList3(): void
    {
        $this->assertEquals('<ul><li>Item 1<ul><li>SubItem 1</li><li>SubItem 2</li></ul></li><li>Item 2</li></ul>', RichTextParser::jsonToHtml(json_decode('[{"type":"list","children":[{"type":"list-item","children":[{"type":"text","text":"Item 1"}]},{"type":"list","children":[{"type":"list-item","children":[{"type":"text","text":"SubItem 1"}]},{"type":"list-item","children":[{"type":"text","text":"SubItem 2"}]}],"format":"unordered"},{"type":"list-item","children":[{"type":"text","text":"Item 2"}]}],"format":"unordered"}]')));
    }

    public function testListWithBold(): void
    {
        $this->assertEquals('<ul><li>Part 1<strong> Bolded text</strong></li></ul>', RichTextParser::jsonToHtml(json_decode('[{"type":"list","children":[{"type":"list-item","children":[{"type":"text","text":"Part 1"},{"type":"text","text":" Bolded text","bold":true}]}],"format":"unordered"}]')));
    }

    public function testImage(): void
    {
        $this->assertEquals('<img src="http://localhost:1337/uploads/medium_placeholder.png" alt="Alternative text" width="750" height="375" loading="lazy" />', RichTextParser::jsonToHtml(json_decode('[{"type":"image","image":{"name":"Name image","alternativeText":"Alternative text","url":"http:\/\/localhost:1337\/uploads\/placeholder.png","width":1000,"height":500,"formats":{"thumbnail":{"name":"thumbnail_Name image","width":245,"height":122,"url":"\/uploads\/thumbnail_placeholder.png"},"small":{"name":"small_Name image","width":500,"height":250,"url":"\/uploads\/small_placeholder.png"},"medium":{"name":"medium_Name image","width":750,"height":375,"url":"\/uploads\/medium_placeholder.png"},"large":{"name":"large_Name image","width":1000,"height":500,"url":"\/uploads\/large_placeholder.png"}}}}]')));
    }

    public function testImageWithoutEnv(): void
    {
        unset($_ENV["STRAPI_URL"]);

        $this->assertEquals('<img src="http://localhost:1337/uploads/placeholder.png" alt="Alternative text" width="1000" height="500" loading="lazy" />', RichTextParser::jsonToHtml(json_decode('[{"type":"image","image":{"name":"Name image","alternativeText":"Alternative text","url":"http:\/\/localhost:1337\/uploads\/placeholder.png","width":1000,"height":500,"formats":{"thumbnail":{"name":"thumbnail_Name image","width":245,"height":122,"url":"\/uploads\/thumbnail_placeholder.png"},"small":{"name":"small_Name image","width":500,"height":250,"url":"\/uploads\/small_placeholder.png"},"medium":{"name":"medium_Name image","width":750,"height":375,"url":"\/uploads\/medium_placeholder.png"},"large":{"name":"large_Name image","width":1000,"height":500,"url":"\/uploads\/large_placeholder.png"}}}}]')));
    }

    public function testLinkInParagraph(): void
    {
        $this->assertEquals('<p>para<a href="https://example.com" target="_blank" rel="noopener noreferrer">Example</a>graph</p>', RichTextParser::jsonToHtml(json_decode('[{"type":"paragraph","children":[{"type":"text","text":"para"},{"type":"link","url":"https://example.com","children":[{"type":"text","text":"Example"}]},{"type":"text","text":"graph"}]}]')));
    }

    public function testQuote(): void
    {
        $this->assertEquals('<blockquote><p>Quote</p></blockquote>', RichTextParser::jsonToHtml(json_decode('[{"type":"quote","children":[{"type":"text","text":"Quote"}]}]')));
    }

    public function testCodeBlock(): void
    {
        $this->assertEquals('<pre><code>Code block</code></pre>', RichTextParser::jsonToHtml(json_decode('[{"type":"code","children":[{"type":"text","text":"Code block"}]}]')));
    }

    public function testListWithLink(): void
    {
        $this->assertEquals('<ul><li>Item 1</li><li>Item 2 <a href="https://hugolevet.fr/" target="_blank" rel="noopener noreferrer">with link</a></li></ul>', RichTextParser::jsonToHtml(json_decode('[{"type":"list","children":[{"type":"list-item","children":[{"type":"text","text":"Item 1"}]},{"type":"list-item","children":[{"type":"text","text":"Item 2 "},{"type":"link","url":"https:\/\/hugolevet.fr\/","children":[{"type":"text","text":"with link"}]},{"text":"","type":"text"}]}],"format":"unordered"}]')));
    }
}
