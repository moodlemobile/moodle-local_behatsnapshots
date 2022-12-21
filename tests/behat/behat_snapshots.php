<?php

use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Mink\Exception\ExpectationException;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

class behat_snapshots extends behat_base {

    protected $createssnapshots;
    protected $currentscenario;
    protected $currentstep;

    /**
     * @BeforeScenario
    */
    public function before_scenario(ScenarioScope $scope) {
        $this->createssnapshots = $scope->getFeature()->hasTag('creates_snapshots') || $scope->getScenario()->hasTag('creates_snapshots');
        $this->currentscenario = $this->get_scenario_slug($scope);
        $this->currentstep = 0;
    }

    /**
     * @BeforeStep
    */
    public function before_step() {
        $this->currentstep++;
    }

    /**
     * @Then The snapshot should match
     */
    public function the_snapshot_should_match() {
        $snapshotname = "{$this->currentscenario}_{$this->currentstep}";
        $snapshotfile = $this->get_snapshots_directory() . DIRECTORY_SEPARATOR . "$snapshotname.html";
        $snapshot = trim($this->get_snapshot());

        if (!file_exists($snapshotfile)) {
            if (!$this->createssnapshots) {
                throw new ExpectationException(
                    "There isn't a snapshot for step {$this->currentstep}, please create one or use the @creates_snapshots tag to mint it",
                    $this->getSession()->getDriver()
                );
            }

            file_put_contents($snapshotfile, $snapshot);

            return;
        }

        $previoussnapshot = trim(file_get_contents($snapshotfile));

        if ($snapshot !== $previoussnapshot) {
            $this->diff_snapshots($snapshot, $previoussnapshot);

            throw new ExpectationException("Snapshot doesn't match", $this->getSession()->getDriver());
        }
    }

    protected function get_snapshot(): string {
        $content = $this->getSession()->getPage()->getContent();

        if (str_starts_with($content, '<html><head></head><body>')) {
            $content = preg_replace("/^<html><head><\/head><body>/", '', $content);
            $content = preg_replace("/<\/body><\/html>$/", '', $content);
        }

        return $content;
    }

    protected function diff_snapshots(string $snapshot, string $previoussnapshot): void {
        $snapshotlines = explode("\n", $snapshot);
        $previoussnapshotlines = explode("\n", $previoussnapshot);

        for ($i = 0; $i < count($snapshotlines); $i++) {
            if ($snapshotlines[$i] === $previoussnapshotlines[$i]) {
                continue;
            }

            echo "before: $previoussnapshotlines[$i]\n";
            echo "   now: $snapshotlines[$i]\n";
        }
    }

    protected function get_snapshots_directory(): string {
        global $CFG;

        $snapshotsdirectory = $CFG->behat_snapshots_path ?? '';

        if (empty($snapshotsdirectory)) {
            throw new Exception('Missing $CFG->behat_snapshots_path config.');
        }

        if (!is_dir($snapshotsdirectory) && !mkdir($snapshotsdirectory, 0777, true)) {
            throw new Exception("Cannot create $snapshotsdirectory directory, check permissions.");
        }

        return $snapshotsdirectory;
    }

    protected function get_scenario_slug(ScenarioScope $scope): string {
        $text = $scope->getFeature()->getTitle() . ' ' . $scope->getScenario()->getTitle();
        $text = trim($text);
        $text = strtolower($text);
        $text = preg_replace('/\s+/', '-', $text);
        $text = preg_replace('/[^a-z0-9-]/', '', $text);

        return $text;
    }

}
