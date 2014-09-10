In the file /framework/composer/autoload_namespaces.php are added the lines:

    'phpManufaktur' => $baseDir . '/extension/phpmanufaktur/',
    'thirdParty' => $baseDir . '/extension/thirdparty/',

and the lines

    if (file_exists($baseDir.'/extension/phpmanufaktur/phpManufaktur/Library/autoload_namespaces.php')) {
	   include_once $baseDir.'/extension/phpmanufaktur/phpManufaktur/Library/autoload_namespaces.php';
	   return array_merge($framework_namespaces, $library_namespaces);
    }
    else {
    	return $framework_namespaces;
    }

to grant the auto loading for the kitFramework.

At /framework are added the files:

    README.md
    VERSION

nothing else is changed.