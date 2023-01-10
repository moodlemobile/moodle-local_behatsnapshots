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

    public function __construct(Session $session, string $name, array $options = []) {
        $this->session = $session;
        $this->name = $name;
        $this->options = $options;
    }

    public function exists(): bool {
        $filepath = $this->get_file_path();

        return file_exists($filepath);
    }

    public function store(): void {
        $filepath = $this->get_file_path();
        $content = $this->get_current_content();

        file_put_contents($filepath, $content);
    }

    public function matches(): bool {
        $storedcontent = $this->get_stored_content();
        $currentcontent = $this->get_current_content();

        return $storedcontent === $currentcontent;
    }

    abstract public function diff(): string;

    protected function store_failure_diffs(): string {
        $directory = $this->get_directory('failures');

        file_put_contents($this->get_file_path('-original', 'failures'), $this->get_stored_content());
        file_put_contents($this->get_file_path('-changed', 'failures'), $this->get_current_content());

        return $directory;
    }

    protected function get_directory(?string $subdirectory = null): string {
        global $CFG;

        $directory = $CFG->behat_snapshots_path ?? '';

        if (empty($directory)) {
            throw new Exception('Missing $CFG->behat_snapshots_path config.');
        }

        $directory = $this->create_directory($directory);

        if (!is_null($subdirectory)) {
            $directory = $this->create_directory($directory . $subdirectory);
        }

        return $directory;
    }

    protected function get_file_path(string $suffix = '', ?string $subdirectory = null, ?string $extension = null): string {
        $filename = $this->get_directory($subdirectory) . $this->name . $suffix;
        $extension ??= $this->extension;

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

    protected function create_directory(string $directory): string {
        if (!str_ends_with($directory, DIRECTORY_SEPARATOR)) {
            $directory .= DIRECTORY_SEPARATOR;
        }

        if (is_dir($directory)) {
            return $directory;
        }

        if (!@mkdir($directory, 0777, true)) {
            throw new Exception("Cannot create $directory directory, check permissions.");
        }

        return $directory;
    }

    abstract protected function load_content();

}
