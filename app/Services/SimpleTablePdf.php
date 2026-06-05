<?php

namespace App\Services;

class SimpleTablePdf
{
    private const PAGE_WIDTH = 842;
    private const PAGE_HEIGHT = 595;
    private const MARGIN = 32;
    private const LINE_HEIGHT = 13;

    public function output(string $title, array $headings, iterable $rows): string
    {
        $pages = $this->pages($title, $headings, $rows);
        $objects = [];

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $pageObjectIds = [];
        $contentObjectIds = [];

        foreach ($pages as $index => $content) {
            $pageObjectIds[] = 3 + ($index * 2);
            $contentObjectIds[] = 4 + ($index * 2);
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', array_map(fn ($id) => "{$id} 0 R", $pageObjectIds)) . '] /Count ' . count($pageObjectIds) . ' >>';

        foreach ($pages as $index => $content) {
            $pageObjectId = $pageObjectIds[$index];
            $contentObjectId = $contentObjectIds[$index];
            $objects[$pageObjectId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . '] /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> /F2 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> >> >> /Contents ' . $contentObjectId . ' 0 R >>';
            $objects[$contentObjectId] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        }

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function pages(string $title, array $headings, iterable $rows): array
    {
        $pages = [];
        $content = [];
        $y = self::PAGE_HEIGHT - self::MARGIN;
        $columnCount = max(1, count($headings));
        $columnWidth = (self::PAGE_WIDTH - (self::MARGIN * 2)) / $columnCount;

        $startPage = function () use (&$content, &$y, $title, $headings, $columnWidth): void {
            $content = [];
            $y = self::PAGE_HEIGHT - self::MARGIN;
            $content[] = $this->text($title, self::MARGIN, $y, 14, true);
            $y -= 24;
            foreach ($headings as $index => $heading) {
                $content[] = $this->text((string) $heading, self::MARGIN + ($index * $columnWidth), $y, 8, true);
            }
            $y -= self::LINE_HEIGHT;
            $content[] = $this->line(self::MARGIN, $y + 5, self::PAGE_WIDTH - self::MARGIN, $y + 5);
        };

        $finishPage = function () use (&$pages, &$content): void {
            $pages[] = implode("\n", $content);
        };

        $startPage();

        foreach ($rows as $row) {
            $values = array_values(is_array($row) ? $row : (array) $row);
            $linesByColumn = [];
            $maxLines = 1;

            foreach ($headings as $index => $heading) {
                $lines = $this->wrap((string) ($values[$index] ?? '-'), max(8, (int) floor($columnWidth / 4.3)));
                $linesByColumn[$index] = $lines;
                $maxLines = max($maxLines, count($lines));
            }

            $rowHeight = $maxLines * self::LINE_HEIGHT;

            if ($y - $rowHeight < self::MARGIN) {
                $finishPage();
                $startPage();
            }

            foreach ($linesByColumn as $index => $lines) {
                foreach ($lines as $lineIndex => $line) {
                    $content[] = $this->text($line, self::MARGIN + ($index * $columnWidth), $y - ($lineIndex * self::LINE_HEIGHT), 8);
                }
            }

            $y -= $rowHeight + 4;
        }

        $finishPage();

        return $pages ?: [''];
    }

    private function wrap(string $text, int $limit): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text)) ?: '-';
        $words = explode(' ', $text);
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            if ($line !== '' && strlen($line . ' ' . $word) > $limit) {
                $lines[] = $line;
                $line = '';
            }

            while (strlen($word) > $limit) {
                $lines[] = substr($word, 0, $limit);
                $word = substr($word, $limit);
            }

            $line = trim($line . ' ' . $word);
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return array_slice($lines, 0, 4);
    }

    private function text(string $text, float $x, float $y, int $size, bool $bold = false): string
    {
        $font = $bold ? 'F2' : 'F1';
        $encoded = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text) ?: $text;
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);

        return "BT /{$font} {$size} Tf 1 0 0 1 " . round($x, 2) . ' ' . round($y, 2) . " Tm ({$escaped}) Tj ET";
    }

    private function line(float $x1, float $y1, float $x2, float $y2): string
    {
        return round($x1, 2) . ' ' . round($y1, 2) . ' m ' . round($x2, 2) . ' ' . round($y2, 2) . ' l S';
    }
}
