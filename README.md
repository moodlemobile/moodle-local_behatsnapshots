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

### Comparing snapshots

In order to compare snapshots, you'll need to use one of the following steps in your tests:

```Gherkin
# Compare the HTML of the current page with the stored snapshot.
Then the HTML should match the snapshot

# Compare the UI of the current page with the stored snapshot.
# This step only works in tests using the @javascript tag.
Then the UI should match the snapshot
```

### Managing snapshots

The first time you run the tests, they will fail because the snapshots don't exist yet. You can add the `@creates_snapshots` tag and it will create snapshots instead of running any comparisons.

Later on, it is possible that you introduce some changes in the code that are intentional and don't cause a regression. In this case, you can either delete the existing snapshots and use `@creates_snapshots` again, or use the `@overrides_snapshots` tag which will override the snapshots, without checking for their existence or running any comparisons.

It is important that you commmit the snapshot files generated using this process in the repository, so that other developers and CI environments rely on these validated snapshots, rather than generating new ones every time.

Also, make sure that you are not including these tags in the repository, and only use them to generate the snapshots locally. The `@overrides_snapshots` tag is specially dangerous, because it won't run any comparisons an can render these tests useless.

## Examples

If you want to see some examples of how to use this plugin and what the snapshots look like, this plugin uses itself in its tests:

- You can find some tests in the [snapshots.feature](tests/behat/snapshots.feature) file.
- You can find some snapshots in the [snapshots](snapshots) folder.
- The plugin is configured in CI using Github Actions, you can find the configuration in [moodle-ci.yml](.github/workflows/moodle-ci.yml).
