<?php

namespace App\Helpers;

class InmoMarkdown
{
    public static function render(string $text): string
    {
        // Escapar HTML primero para seguridad
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Encabezados ## y ###
        $text = preg_replace('/^### (.+)$/m', '<p style="font-size:12px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.05em;margin:10px 0 4px;">$1</p>', $text);
        $text = preg_replace('/^## (.+)$/m',  '<p style="font-size:14px;font-weight:800;color:#1e293b;margin:10px 0 4px;">$1</p>', $text);

        // Negrita **texto**
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong style="font-weight:700;color:#0f172a;">$1</strong>', $text);

        // Cursiva *texto*
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);

        // Líneas de lista: - item o • item
        // Agrupa líneas consecutivas de lista en un <ul>
        $lines = explode("\n", $text);
        $output = [];
        $inList = false;

        foreach ($lines as $line) {
            $isItem = preg_match('/^[-•]\s+(.+)$/', $line, $m);

            if ($isItem) {
                if (!$inList) {
                    $output[] = '<ul style="margin:6px 0;padding-left:0;list-style:none;">';
                    $inList = true;
                }
                $output[] = '<li style="display:flex;gap:7px;align-items:flex-start;padding:2px 0;font-size:13px;"><span style="color:#1e3a8a;flex-shrink:0;margin-top:2px;">›</span><span>' . $m[1] . '</span></li>';
            } else {
                if ($inList) {
                    $output[] = '</ul>';
                    $inList = false;
                }

                $trimmed = trim($line);

                // Línea vacía → espacio visual
                if ($trimmed === '') {
                    $output[] = '<div style="height:6px;"></div>';
                }
                // Separador ---
                elseif ($trimmed === '---') {
                    $output[] = '<hr style="border:none;border-top:1px solid #e2e8f0;margin:8px 0;">';
                }
                else {
                    $output[] = '<p style="margin:0;line-height:1.65;">' . $line . '</p>';
                }
            }
        }

        if ($inList) {
            $output[] = '</ul>';
        }

        return implode('', $output);
    }
}
