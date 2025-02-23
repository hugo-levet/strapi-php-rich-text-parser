<?php

namespace HugoLevet\StrapiPhpRichTextParser;

class RichTextParser
{
    public static function parseText($data): string
    {
        $html_content = '';

        if (!empty($data->bold) && $data->bold === true) {
            $html_content .= '<strong>' . htmlspecialchars($data->text) . '</strong>';
        } elseif (!empty($data->italic) && $data->italic === true) {
            $html_content .= '<em>' . htmlspecialchars($data->text) . '</em>';
        } elseif (!empty($data->underline) && $data->underline === true) {
            $html_content .= '<u>' . htmlspecialchars($data->text) . '</u>';
        } elseif (!empty($data->strikethrough) && $data->strikethrough === true) {
            $html_content .= '<del>' . htmlspecialchars($data->text) . '</del>';
        } elseif (!empty($data->code) && $data->code === true) {
            $html_content .= '<code>' . htmlspecialchars($data->text) . '</code>';
        } else {
            $html_content .= htmlspecialchars($data->text);
        }

        return $html_content;
    }

    public static function parseList($value)
    {
        $html_content = '';
        $item_tag = $value->format == 'unordered' ? 'ul' : 'ol';
        $html_content .= '<' . $item_tag . '>';
        foreach ($value->children as $key => $child) {
            if ($child->type == 'list-item') {
                $html_content .= '<li>';
                foreach ($child->children as $key => $subchild) {
                    $html_content .= RichTextParser::parseBlockText($subchild);
                }
                $html_content .= '</li>';
            } elseif ($child->type == 'list') {
                // remove last </li> tag
                $html_content = substr($html_content, 0, -5);
                // parse nested list
                $html_content .= RichTextParser::parseList($child) . '</li>';
            } else {
                $html_content .= '<!-- ' . $child->type . ' is not implemented yet -->';
                // not implemented
            }
        }
        $html_content .= '</' . $item_tag . '>';
        return $html_content;
    }

    private static function isTweet($data): bool
    {
        return strpos($data->url, 'https://x.com') !== false;
    }

    private static function embedTweet($url): string | null
    {
        $apiUrl = 'https://publish.twitter.com/oembed?url=' . urlencode($url);

        try {
            $response = file_get_contents($apiUrl);
        } catch (\Exception $e) {
            return null;
        }

        $tweetData = json_decode($response, true);

        return $tweetData['html'];
    }

    public static function parseBlockText($data): string
    {
        $html_content = '';
        $type = $data->type;

        if ($type == 'text') {
            $html_content .= RichTextParser::parseText($data);
        } elseif ($type == 'link') {
            if (RichTextParser::isTweet($data)) {
                $embed_tweet = RichTextParser::embedTweet($data->url);
                $html_content .= $embed_tweet ? $embed_tweet : '<a href="' . $data->url . '" target="_blank" rel="noopener noreferrer">' . RichTextParser::parseText($data->children[0]) . '</a>';
            } else {
                $is_external_link = $data->url[0] !== '/';
                $html_content .=
                    '<a href="' .
                    $data->url .
                    '" ' .
                    ($is_external_link ? 'target="_blank" rel="noopener noreferrer"' : '') .
                    '>' .
                    RichTextParser::parseText($data->children[0]) .
                    '</a>';
            }
        } else {
            $html_content .= '<!-- ' . $data->type . ' is not implemented yet -->';
            // not implemented
        }

        return $html_content;
    }
    public

    static function jsonToHtml($json, $shortcodes = []): string
    {
        $html_content = '';
        foreach ($json as $key => $value) {
            switch ($value->type) {
                case 'paragraph':
                    // check if it's a shortcode
                    if (preg_match('/\[(\w+)\]/', $value->children[0]->text, $matches)) {
                        $shortcode = $matches[1];
                        if (isset($shortcodes[$shortcode])) {
                            $html_content .= call_user_func($shortcodes[$shortcode], $value);
                            break;
                        } else {
                            $html_content .= '<!-- ' . $shortcode . ' shortcode is not implemented yet -->';
                            // not implemented                            
                        }
                    }
                    $html_to_add = '<p>';
                    foreach ($value->children as $key => $child) {
                        $html_to_add .= RichTextParser::parseBlockText($child);
                    }
                    $html_to_add .= '</p>';

                    if ($html_to_add === '<p></p>') {
                        $html_to_add = '<br>';
                    }

                    $html_content .= $html_to_add;
                    break;

                case 'image':
                    $image = $value->image;
                    $imageUrl = $value->image->url;
                    if (isset($_ENV["STRAPI_URL"])) {
                        if (isset($value->image->formats->large)) {
                            $image = $value->image->formats->large;
                        } else if (isset($value->image->formats->medium)) {
                            $image = $value->image->formats->medium;
                        } else if (isset($value->image->formats->small)) {
                            $image = $value->image->formats->small;
                        } else if (isset($value->image->formats->thumbnail)) {
                            $image = $value->image->formats->thumbnail;
                        } else {
                            $image = null;
                        }
                        if ($image) {
                            $imageUrl = $_ENV["STRAPI_URL"] . $image->url;
                        } else {
                            $image = $value->image;
                        }
                    }
                    $html_content .=
                        '<img src="' .
                        $imageUrl .
                        '" alt="' .
                        $value->image->alternativeText .
                        '" width="' .
                        $image->width .
                        '" height="' .
                        $image->height .
                        '" loading="lazy" />';
                    break;

                case 'heading':
                    if ($value->children[0]->type == 'text') {
                        $html_content .=
                            '<h' .
                            $value->level .
                            '>' .
                            RichTextParser::parseText($value->children[0]) .
                            '</h' .
                            $value->level .
                            '>';
                    } else {
                        $html_content .= '<!-- ' . $value->type . ' is not implemented yet -->';
                        // not implemented
                    }
                    break;

                case 'list':
                    $html_content .= RichTextParser::parseList($value);
                    break;

                case 'quote':
                    $html_content .= '<blockquote>';
                    $html_content .= '<p>';
                    foreach ($value->children as $key => $child) {
                        $html_content .= RichTextParser::parseBlockText($child);
                    }
                    $html_content .= '</p>';
                    $html_content .= '</blockquote>';
                    break;

                case 'code':
                    $html_content .= '<pre><code>';
                    $child = $value->children[0];
                    if ($child->type == 'text') {
                        $html_content .=  htmlspecialchars($child->text);
                    } else {
                        $html_content .= '<!-- ' . $child->type . ' is not implemented yet -->';
                        // not implemented
                    }
                    $html_content .= '</code></pre>';
                    break;

                default:
                    $html_content .= '<!-- ' . $value->type . ' is not implemented yet -->';
                    // not implemented
                    break;
            }
        }

        return $html_content;
    }
}
