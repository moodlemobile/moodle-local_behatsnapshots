# Behat Snapshots Plugin

This plugin adds custom Behat steps to test against visual and HTML regressions.

Visual and HTML regressions are specially hard to detect with automated testing, because things like style changes can have unintended consequences that don't break functionality but cause small graphical glitches. And accesibility changes in the HTML can also go unnoticed. The way snapshot testing works is that snapshot files are created during development, and committed alongside the source code in the repository. Then, in CI environments and during further development, new snapshots are generated and compared against the previous ones. If anything has changed unintentionally, it will thus be detected and reported.

## Configuration and usage

In order to use the custom steps, you need to include this plugin in your Moodle installation. This is only necessary for testing and development environments; make sure not to include it in production.

You'll also need to add the following attributes in your `config.php`:

```php
// Configure where snapshot files will be created and read from.
$CFG->behat_snapshots_path = '/var/www/html/local/behatsnapshots/snapshots';

// (optional) Threshold to consider image differences a regression.
// This value will be used for comparing UI snapshots, and is only relevant
// when the imagick PHP extension is installed.
//
// Default value: 0.
$CFG->behat_snapshots_image_threshold = 0.001;
```

Once that is configured, you can start using the following steps in your tests:

```Gherkin
# Compare the HTML of the current page with the stored snapshot.
Then the HTML should match the snapshot

# Compare the UI of the current page with the stored snapshot.
# This step only works in tests using the @javascript tag.
Then the UI should match the snapshot
```

The first time you run the tests, this command will fail because the snapshots don't exist yet. In order to create them, you can add the `@creates_snapshots` tag and run them again. This time, the snapshot files will be created. It's not recommended to keep this tag in tests at all times; it should only be used for minting new snapshots when they are missing.

Once the snapshots are created, you have to commit them with your source code because they will be used for comparison in CI.

## Examples

If you want to see some examples of how to use this plugin and what the snapshots look like, this plugin uses itself in its tests:

- You can find some tests in the [snapshots.feature](tests/behat/snapshots.feature) file.
- You can find some snapshots in the [snapshots](snapshots) folder.
- The plugin is configured in CI using Github Actions, you can find the configuration in [moodle-ci.yml](.github/workflows/moodle-ci.yml).
