<?php

namespace local_behatsnapshots\snapshots;

use local_behatsnapshots\diff;

class html_snapshot extends behat_snapshot {

    protected $extension = 'html';

    /**
     * @var string|null
     */
    protected $diff;

    public function matches(): bool {
        if (parent::matches()) {
            return true;
        }

        $storedhtml = $this->get_stored_content();
        $currenthtml = $this->get_current_content();
        $this->diff = diff::html($storedhtml, $currenthtml, $this->get_replacements());

        return empty($this->diff);
    }

    public function diff(): string {
        $failuresdirectory = $this->store_failure_diffs();

        file_put_contents($this->get_file_path(['extension' => 'diff', 'directory' => $failuresdirectory]), $this->diff);

        if (strlen($this->diff) > 300) {
            return "You can compare the differences looking at the files in $failuresdirectory.";
        }

        return $this->diff;
    }

    protected function load_content() {
        $content = $this->session->getPage()->getContent();
        $content = $this->clean_padding_content($content);

        if ($this->is_mobile()) {
            $content = $this->normalize_mobile_app_content($content);
        }

        return trim($content);
    }

    protected function get_replacements(): array {
        $replacements = @json_decode(get_config('local_behatsnapshots', 'replacements'));

        if (isset($replacements)) {
            return $replacements;
        }

        if ($this->is_mobile()) {
            return [
                '/ style=""/' => '',
                '/ role="tablist"/' => '',
            ];
        }

        return [];
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

        return $content;
    }

}
