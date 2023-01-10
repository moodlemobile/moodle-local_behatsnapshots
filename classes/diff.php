<?php

namespace local_behatsnapshots;

class diff {

    public static function html(string $original, string $changed, array $replacements = []): ?string {
        $originallines = explode("\n", static::normalize_html($original, $replacements));
        $changedlines = explode("\n", static::normalize_html($changed, $replacements));
        $totallines = max(count($originallines), count($changedlines));
        $diffcounter = 0;
        $diff = '';

        for ($line = 0; $line < $totallines; $line++) {
            $originalline = $originallines[$line] ?? '';
            $changedline = $changedlines[$line] ?? '';
            $linesdiff = static::text($originalline, $changedline);

            if (empty($linesdiff)) {
                continue;
            }

            $diffcounter++;
            $linenumber = $line + 1;
            $diff .= "#$diffcounter (line $linenumber)\n";
            $diff .= "$linesdiff\n";
        }

        if ($diffcounter === 0) {
            return null;
        }

        return $diff;
    }

    public static function text(string $original, string $changed): ?string {
        // Simple comparison.
        if ($original === $changed) {
            return null;
        }

        // Find regexes.
        preg_match_all('/(\[\[(.*?)\]\])(?:[^\]]|$)/', $original, $matches);

        $regexescount = count($matches[0]);

        if ($regexescount === 0) {
            return static::get_text_diff($original, $changed);
        }

        // Regex comparison.
        $regex = preg_quote($original, '/');

        for ($i = 0; $i < $regexescount; $i++) {
            $regex = str_replace(preg_quote($matches[1][$i], '/'), $matches[2][$i], $regex);
        }

        if (preg_match("/^$regex$/", $changed)) {
            return null;
        }

        return static::get_text_diff($original, $changed);
    }

    private static function normalize_html(string $html, array $replacements = []): string {
        // Apply replacements.
        foreach ($replacements as $regex => $replacement) {
            $html = preg_replace($regex, $replacement, $html);
        }

        // Sort classes.
        preg_match_all('/class="([^"]+)"/', $html, $matches);

        foreach ($matches[1] as $match) {
            $classes = array_filter(explode(' ', $match), function($class) {
                return !str_starts_with($class, 'ng-tns');
            });

            sort($classes);

            $sortedclasses = implode(' ', $classes);
            $html = str_replace("class=\"$match\"", "class=\"$sortedclasses\"", $html);
        }

        return $html;
    }

    private static function get_text_diff(string $original, string $changed): string {
        return "< $original\n---\n> $changed";
    }

}
