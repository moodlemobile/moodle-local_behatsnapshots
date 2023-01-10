<?php

use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Mink\Exception\ExpectationException;
use local_behatsnapshots\snapshots\behat_snapshot;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

class behat_snapshots extends behat_base {

    /**
     * @var bool
     */
    protected $createssnapshots;

    /**
     * @var bool
     */
    protected $overridessnapshots;

    /**
     * @var bool
     */
    protected $ismobileapp;

    /**
     * @var string
     */
    protected $currentscenario;

    /**
     * @var int
     */
    protected $currentstep;

    /**
     * @BeforeScenario
    */
    public function before_scenario(ScenarioScope $scope) {
        $this->createssnapshots = $scope->getFeature()->hasTag('creates_snapshots') || $scope->getScenario()->hasTag('creates_snapshots');
        $this->overridessnapshots = $scope->getFeature()->hasTag('overrides_snapshots') || $scope->getScenario()->hasTag('overrides_snapshots');
        $this->ismobileapp = $scope->getFeature()->hasTag('app') || $scope->getScenario()->hasTag('app');
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
     * @Then the :type should match the snapshot
     */
    public function the_snapshot_should_match(string $type) {
        $snapshot = $this->create_snapshot($type);

        if ($this->overridessnapshots) {
            $snapshot->store($this->getSession());

            return;
        }

        if (!$snapshot->exists()) {
            if (!$this->createssnapshots) {
                throw new ExpectationException(
                    "There isn't a snapshot for step {$this->currentstep}, use the @creates_snapshots tag to create it.",
                    $this->getSession()->getDriver()
                );
            }

            $snapshot->store($this->getSession());
            return;
        }

        if (!$snapshot->matches()) {
            echo $snapshot->diff();

            throw new ExpectationException("Snapshots don't match", $this->getSession()->getDriver());
        }
    }

    protected function create_snapshot(string $type): behat_snapshot {
        $type = strtolower($type);
        $snapshotclass = 'local_behatsnapshots\\snapshots\\'.$type.'_snapshot';

        return new $snapshotclass($this->getSession(), "{$this->currentscenario}_{$this->currentstep}", ['mobile' => $this->ismobileapp]);
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
