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

    public static function parseBlockText($data): string
    {
        $html_content = '';
        $type = $data->type;

        if ($type == 'text') {
            $html_content .= RichTextParser::parseText($data);
        } elseif ($type == 'link') {
            $is_external_link = $data->url[0] !== '/';
            $html_content .=
                '<a href="' .
                $data->url .
                '" ' .
                ($is_external_link ? 'target="_blank" rel="noopener noreferrer"' : '') .
                '>' .
                RichTextParser::parseText($data->children[0]) .
                '</a>';
        } else {
            $html_content .= '<!-- ' . $data->type . ' is not implemented yet -->';
            // not implemented
        }

        return $html_content;
    }
    public

    static function jsonToHtml($json): string
    {
        $html_content = '';
        foreach ($json as $key => $value) {
            switch ($value->type) {
                case 'paragraph':
                    $html_content .= '<p>';
                    foreach ($value->children as $key => $child) {
                        $html_content .= RichTextParser::parseBlockText($child);
                    }
                    $html_content .= '</p>';
                    break;

                case 'image':
                    $image = $value->image->formats->medium;
                    $html_content .=
                        '<img src="' .
                        $_ENV["STRAPI_URL"] .
                        $image->url .
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
                    foreach ($value->children as $key => $child) {
                        if ($child->type == 'text') {
                            $html_content .= '<p>' . RichTextParser::parseText($child) . '</p>';
                        } else {
                            $html_content .= '<!-- ' . $child->type . ' is not implemented yet -->';
                            // not implemented
                        }
                    }
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
