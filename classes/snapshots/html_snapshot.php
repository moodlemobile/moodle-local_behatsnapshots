<?php

namespace local_behatsnapshots\snapshots;

class html_snapshot extends behat_snapshot {

    protected $extension = 'html';

    public function diff(): string {
        $storedhtml = $this->get_stored_content();
        $currenthtml = $this->get_current_content();
        $storedhtmllines = explode("\n", $storedhtml);
        $currenthtmllines = explode("\n", $currenthtml);

        return array_reduce(
            range(0, count($currenthtmllines)),
            function ($diff, $line) use ($storedhtmllines, $currenthtmllines) {
                if ($currenthtmllines[$line] !== $storedhtmllines[$line]) {
                    $diff .= "before: $storedhtmllines[$line]\n";
                    $diff .= "   now: $currenthtmllines[$line]\n";
                }

                return $diff;
            },
            ''
        );
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
