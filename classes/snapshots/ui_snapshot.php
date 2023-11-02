<?php

namespace local_behatsnapshots\snapshots;

use Behat\Mink\Exception\UnsupportedDriverActionException;
use Exception;
use local_behatsnapshots\graphics\image;
use local_behatsnapshots\graphics\image_diff;

class ui_snapshot extends behat_snapshot {

    protected $extension = 'png';

    /**
     * @var image
     */
    protected $currentimage;

    /**
     * @var image
     */
    protected $storedimage;

    /**
     * @var image_diff
     */
    protected $diff;

    public function matches(): bool {
        $threshold = get_config('local_behatsnapshots', 'image_threshold');
        $threshold = $threshold ? floatval($threshold) : 0.05;
        $this->diff = $this->get_stored_image()->compare($this->get_current_image());

        return $this->diff->percentage() <= $threshold;
    }

    public function diff(): string {
        $failuresdirectory = $this->store_failure_diffs();

        $this->diff->save($this->get_file_path(['suffix' => '-diff', 'directory' => $failuresdirectory]));

        return "Snapshots are {$this->diff->percentage()}% different.\n" .
            "You can compare the differences looking at the files in $failuresdirectory.";
    }

    protected function load_content() {
        try {
            return $this->session->getScreenshot();
        } catch (UnsupportedDriverActionException $e) {
            $driver = get_class($this->session->getdriver());

            throw new Exception("Screenshots are not supported by the current driver: $driver (did you forget to use the @javascript tag?)");
        }
    }

    protected function get_stored_image(): image {
        if (is_null($this->storedimage)) {
            $this->storedimage = image::from_file($this->get_file_path());
        }

        return $this->storedimage;
    }

    protected function get_current_image(): image {
        if (is_null($this->currentimage)) {
            $this->currentimage = image::from_blob($this->get_current_content());
        }

        return $this->currentimage;
    }

}
