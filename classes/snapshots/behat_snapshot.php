<?php

namespace local_behatsnapshots\snapshots;

use Behat\Mink\Session;
use Exception;

abstract class behat_snapshot {

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $snapshotspath;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var string|null
     */
    protected $extension = null;

    /**
     * @var mixed
     */
    protected $currentcontent;

    /**
     * @var mixed
     */
    protected $storedcontent;

    public function __construct(Session $session, string $name, string $snapshotspath, array $options = []) {
        $this->session = $session;
        $this->name = $name;
        $this->snapshotspath = $snapshotspath;
        $this->options = $options;
    }

    public function exists(): bool {
        $filepath = $this->get_file_path();

        return file_exists($filepath);
    }

    public function store(): void {
        $filepath = $this->get_file_path();
        $content = $this->get_current_content();
        $existed = file_exists($filepath);

        file_put_contents($filepath, $content);

        if (!$existed) {
            chmod($filepath, 0666);
        }
    }

    public function matches(): bool {
        $storedcontent = $this->get_stored_content();
        $currentcontent = $this->get_current_content();

        return $storedcontent === $currentcontent;
    }

    abstract public function diff(): string;

    protected function is_mobile(): bool {
        return $this->options['mobile'] ?? false;
    }

    protected function store_failure_diffs(): string {
        global $CFG;

        $failuresdirectory = $CFG->behat_snapshots_failures_path ?? '';

        if (empty($failuresdirectory)) {
            $snapshotsdirectory = $this->create_directory();
            $failuresdirectory = "{$snapshotsdirectory}failures/";
        }

        $failuresdirectory = $this->create_directory($failuresdirectory);

        file_put_contents($this->get_file_path(['suffix' => '-original', 'directory' => $failuresdirectory]), $this->get_stored_content());
        file_put_contents($this->get_file_path(['suffix' => '-changed', 'directory' => $failuresdirectory]), $this->get_current_content());

        return $failuresdirectory;
    }

    protected function get_file_path(array $options = []): string {
        $suffix = $options['suffix'] ?? '';
        $extension = $options['extension'] ?? $this->extension;
        $directory = $options['directory'] ?? $this->create_directory();
        $filename = $directory . $this->name . $suffix;

        if (!is_null($extension)) {
            $filename .= ".{$extension}";
        }

        return $filename;
    }

    protected function get_stored_content() {
        if (is_null($this->storedcontent)) {
            $filepath = $this->get_file_path();

            $this->storedcontent = trim(file_get_contents($filepath));
        }

        return $this->storedcontent;
    }

    protected function get_current_content() {
        if (is_null($this->currentcontent)) {
            $this->currentcontent = $this->load_content();
        }

        return $this->currentcontent;
    }

    protected function create_directory(?string $directory = null): string {
        global $CFG;

        $directory ??= $CFG->behat_snapshots_path ?? $this->snapshotspath;

        if (!str_ends_with($directory, DIRECTORY_SEPARATOR)) {
            $directory .= DIRECTORY_SEPARATOR;
        }

        if (is_dir($directory)) {
            return $directory;
        }

        if (!@mkdir($directory, 0777, true)) {
            throw new Exception("Cannot create $directory directory.");
        }

        return $directory;
    }

    abstract protected function load_content();

}
