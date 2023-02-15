<?php

namespace local_behatsnapshots\snapshots;

use Imagick;

class ui_snapshot extends behat_snapshot {

    protected $extension = 'png';

    /**
     * @var Imagick
     */
    protected $currentimage;

    /**
     * @var Imagick
     */
    protected $storedimage;

    /**
     * @var float
     */
    protected $diff;

    /**
     * @var Imagick
     */
    protected $diffimage;

    public function matches(): bool {
        if (!$this->imagick_available()) {
            return parent::matches();
        }

        global $CFG;

        [$diffimage, $diff] = $this->get_stored_image()->compareImages($this->get_current_image(), Imagick::METRIC_ROOTMEANSQUAREDERROR);

        $threshold = $CFG->behat_snapshots_image_threshold ?? 0.005;
        $this->diff = $diff;
        $this->diffimage = $diffimage;

        return $diff <= $threshold;
    }

    public function diff(): string {
        $failuresdirectory = $this->store_failure_diffs();

        if (!$this->imagick_available()) {
            return "You can compare the differences looking at the files in $failuresdirectory.\n" .
                "Imagick extension is missing, install it if you want to get better image diffs.";
        }

        file_put_contents($this->get_file_path('-diff', 'failures'), $this->diffimage);

        return "Snapshots are {$this->diff} different.\n" .
            "You can compare the differences looking at the files in $failuresdirectory.";
    }

    protected function imagick_available(): bool {
        return class_exists('Imagick');
    }

    protected function load_content() {
        return $this->session->getScreenshot();
    }

    protected function get_stored_image() {
        if (is_null($this->storedimage)) {
            $this->storedimage = new Imagick($this->get_file_path());
        }

        return $this->storedimage;
    }

    protected function get_current_image() {
        if (is_null($this->currentimage)) {
            $this->currentimage = new Imagick();

            $this->currentimage->readImageBlob($this->get_current_content());
        }

        return $this->currentimage;
    }

}
