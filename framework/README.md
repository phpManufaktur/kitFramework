The framework contains SILEX 1.1.0 (2013-05-04)

In the file /framework/composer/autoload_namespaces.php are added the lines:

    'phpManufaktur' => $baseDir . '/extension/phpmanufaktur/',
    'thirdParty' => $baseDir . '/extension/thirdparty/',
    'dflydev' => $baseDir . '/extension/framework/dflydev/',
    'Nicl' => $baseDir . '/extension/framework/nicl/',
    'Carbon' => $baseDir . '/extension/framework/carbon/',

to grant the auto loading for the kitFramework.

At /framework are added the files:

    README.md
    VERSION

nothing else is changed.