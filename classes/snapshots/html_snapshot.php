<?php

namespace local_behatsnapshots\snapshots;

class html_snapshot extends behat_snapshot {

    protected $extension = 'html';

    public function diff(): string {
        $failuresdirectory = $this->store_failure_diffs();
        $storedhtml = $this->get_stored_content();
        $currenthtml = $this->get_current_content();
        $storedhtmllines = explode("\n", $storedhtml);
        $currenthtmllines = explode("\n", $currenthtml);
        $totallines = max(count($storedhtmllines), count($currenthtmllines));
        $diffcounter = 0;
        $diff = '';

        for ($line = 0; $line < $totallines; $line++) {
            $storedline = $storedhtmllines[$line] ?? '';
            $currentline = $currenthtmllines[$line] ?? '';

            if ($storedline === $currentline) {
                continue;
            }

            $diffcounter++;

            $diff .= "#$diffcounter\n";
            $diff .= "< $storedline\n";
            $diff .= "---\n";
            $diff .= "> $currentline\n";
        }

        file_put_contents($this->get_file_path('', 'failures', 'diff'), $diff);

        if ($diffcounter > 6 || strlen($diff) > 300) {
            return "You can compare the differences looking at the files in $failuresdirectory.";
        }

        return $diff;
    }

    protected function load_content() {
        $content = $this->session->getPage()->getContent();
        $content = $this->clean_padding_content($content);

        if ($this->options['mobile'] ?? false) {
            $content = $this->normalize_mobile_app_content($content);
        }

        return trim($content);
    }

    protected function clean_padding_content(string $content): string {
        // Depending on the environment the <html>, <head>, and <body> elements are included in order to wrap the HTML
        // content rendered by Moodle. Given that sometimes they are missing, but they are irrelevant for the HTML snapshots,
        // we'll remove them if they exist to avoid unnecessary failures.

        if (!str_starts_with($content, '<html><head></head><body>')) {
            return $content;
        }

        $content = preg_replace("/^<html><head><\/head><body>/", '', $content);
        $content = preg_replace("/<\/body><\/html>$/", '', $content);

        return $content;
    }

    protected function normalize_mobile_app_content(string $content): string {
        global $CFG;

        // Normalize lines with variable text.
        $content = str_replace($CFG->behat_wwwroot, 'http://moodle.dev', $content);
        $content = str_replace($CFG->behat_ionic_wwwroot, 'http://moodleapp.dev', $content);
        $content = preg_replace("/<head>.*<\/head>/s", '', $content);
        $content = preg_replace("/_ng(host|content)[^=]+=\"[^\"]*\"/", '', $content);
        $content = preg_replace("/^<script src=\"runtime(\.\w+)?\.js\".*$/m", '', $content);
        $content = preg_replace("/\/persistent\/sites\/\w+\/filepool\//", '/persistent/sites/[siteid]/filepool/', $content);

        // Sort class names.
        preg_match_all('/class="([^"]+)"/', $content, $matches);

        foreach ($matches[1] as $match) {
            $classes = array_filter(explode(' ', $match), function($class) {
                return !str_starts_with($class, 'ng-tns');
            });

            sort($classes);

            $sortedclasses = implode(' ', $classes);
            $content = str_replace("class=\"$match\"", "class=\"$sortedclasses\"", $content);
        }

        return $content;
    }

}
