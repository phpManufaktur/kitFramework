<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if ('á' != "\xc3\xa1") {
	// the language files must be saved as UTF-8 (without BOM)
	throw new \Exception('The language file '.__FILE__.' is damaged, it must be saved UTF-8 encoded!');
}

return array(
		'Bad credentials'
			=> 'Ungültige Angaben!',
		'<p>It seem\'s that this is the first start of the <b>kitFramework</b>.</p><p>To protect the framework you must login with administrator privileges, please use your CMS account for it.</p><p>After login the kitFramework will connect to Github, download additional base components and configure itself.</p>'
			=> '<p>Dies scheint der erste Aufruf des <b>kitFramework</b> zu sein.</p><p>Um das Framework vor unerwünschten Zugriffen zu schützen, ist es erforderlich dass Sie sich mit Administrator Rechten anmelden. Bitte nutzen Sie hierfür Ihre WebsiteBaker/LEPTON Zugangsdaten.</p><p>Nach Ihrer Anmeldung wird sich das kitFramework mit GitHub verbinden, aktuelle Basiskomponenten herunterladen und sich für die erste Verwendung konfigurieren.</p>',
		'kitFramework - Login'
			=> 'kitFramework - Anmeldung',
		'kitFramework - Logout'
			=> 'kitFramework - Abmeldung',
		'Login'
			=> 'Anmelden',
		'Logout'
			=> 'Abmelden',
		'Password'
			=> 'Passwort',
		'Username'
			=> 'Benutzername',
		'Welcome'
			=> 'Herzlich Willkommen!'
		);