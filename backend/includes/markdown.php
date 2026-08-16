<?php
function render_markdown_inline($text)
{
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^\s)]+)\)/', function ($matches) {
        $url = html_entity_decode($matches[2], ENT_QUOTES, 'UTF-8');

        if (!preg_match('/^https?:\/\//i', $url)) {
            return $matches[1] . ' (' . $matches[2] . ')';
        }

        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $safeUrl = str_replace(['*', '+'], ['&#42;', '&#43;'], $safeUrl);

        return '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer">' . $matches[1] . '</a>';
    }, $text);

    $text = preg_replace('/\*\*([^*\n]+)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\+\+([^+\n]+)\+\+/', '<u>$1</u>', $text);
    $text = preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/', '<em>$1</em>', $text);

    return $text;
}

function render_markdown($content)
{
    $escaped = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    $lines = preg_split('/\r\n|\r|\n/', $escaped);
    $html = '';
    $paragraph = [];
    $listType = '';

    $closeParagraph = function () use (&$html, &$paragraph) {
        if (!empty($paragraph)) {
            $html .= '<p>' . render_markdown_inline(implode('<br>', $paragraph)) . '</p>';
            $paragraph = [];
        }
    };

    $closeList = function () use (&$html, &$listType) {
        if ($listType !== '') {
            $html .= '</' . $listType . '>';
            $listType = '';
        }
    };

    foreach ($lines as $line) {
        if (trim($line) === '') {
            $closeParagraph();
            $closeList();
            continue;
        }

        if (preg_match('/^(#{1,3})\s+(.+)$/', $line, $matches)) {
            $closeParagraph();
            $closeList();
            $level = strlen($matches[1]);
            $html .= '<h' . $level . '>' . render_markdown_inline($matches[2]) . '</h' . $level . '>';
            continue;
        }

        if (preg_match('/^[-*]\s+(.+)$/', $line, $matches)) {
            $closeParagraph();

            if ($listType !== 'ul') {
                $closeList();
                $html .= '<ul>';
                $listType = 'ul';
            }

            $html .= '<li>' . render_markdown_inline($matches[1]) . '</li>';
            continue;
        }

        if (preg_match('/^\d+\.\s+(.+)$/', $line, $matches)) {
            $closeParagraph();

            if ($listType !== 'ol') {
                $closeList();
                $html .= '<ol>';
                $listType = 'ol';
            }

            $html .= '<li>' . render_markdown_inline($matches[1]) . '</li>';
            continue;
        }

        $closeList();
        $paragraph[] = $line;
    }

    $closeParagraph();
    $closeList();

    return $html;
}

function markdown_excerpt($content, $length = 180)
{
    $spacedBlocks = preg_replace('/<\/?(h[1-3]|p|ul|ol|li|br)\b[^>]*>/i', ' ', render_markdown($content));
    $withoutTags = strip_tags($spacedBlocks);
    $plainText = trim(preg_replace('/\s+/', ' ', html_entity_decode($withoutTags, ENT_QUOTES, 'UTF-8')));

    if (mb_strlen($plainText) <= $length) {
        return $plainText;
    }

    return rtrim(mb_substr($plainText, 0, $length)) . '...';
}
