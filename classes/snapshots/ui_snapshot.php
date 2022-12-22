<?php

namespace local_behatsnapshots\snapshots;

class ui_snapshot extends behat_snapshot {

    protected $extension = 'png';

    public function diff(): string {
        $directory = $this->get_directory('failures');

        file_put_contents($this->get_file_path('-original', 'failures'), $this->get_stored_content());
        file_put_contents($this->get_file_path('-changed', 'failures'), $this->get_current_content());

        return "Find snapshot differences in $directory";
    }

    protected function load_content() {
        return $this->session->getScreenshot();
    }

}
