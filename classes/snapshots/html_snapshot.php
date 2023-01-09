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

        if (str_starts_with($content, '<html><head></head><body>')) {
            $content = preg_replace("/^<html><head><\/head><body>/", '', $content);
            $content = preg_replace("/<\/body><\/html>$/", '', $content);
        }

        return trim($content);
    }

}
