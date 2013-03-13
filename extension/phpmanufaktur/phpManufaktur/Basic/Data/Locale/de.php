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
    throw new \Exception('The language file ' . __FILE__ . ' is damaged, it must be saved UTF-8 encoded!');
}

return array(
    'Bad credentials' =>
        'Die Angaben sind unvollständig oder ungültig!',
    '<p>Can\'t create a new GUID as long the last GUID is not expired. You must wait 24 hours between the creation of new passwords.</p>' =>
        '<p>Es kann keine neue GUID erzeugt werden, solange die letzte noch gültig ist. Sie können das Anlegen eines neuen Passwort nur einmal innerhalb von 24 Stunden anfordern!</p>',
    'Create a new password' =>
        'Ein neues Password anlegen',
    'Email' =>
        'E-Mail',
    'Forgot your password?' =>
        'Passwort vergessen?',
    '<p>Hello %name%,<br />you have asked to create a new password for the kitFramework hosted at %server%.</p>' =>
        '<p>Hallo %name%,<br />Sie haben darum gebeten ein neues Passwort für das kitFramework auf %server% zu erhalten.</p>',
    '<p>Hello %name%,</p><p>you want to change your password, so please type in a new one, repeat it and submit the form.</p><p>If you won\'t change your password just leave this dialog.</p>' =>
        '<p>Hallo %name%,</p><p>Sie möchten Ihr Passwort ändern, bitte geben Sie das neue Passwort ein, wiederholen Sie es zur Sicherheit und schicken Sie das Formular ab.</p><p>Falls Sie Ihr Passwort nicht ändern möchten, verlassen Sie bitte einfach diesen Dialog.</p>',
    '<p>If you have forgotten your password, you can order a link to create a new one.</p><p>Please type in the email address assigned to your account and submit the form.</p>' =>
        '<p>Falls Sie Ihr Passwort vergessen haben, können Sie einen Link anfordern um ein neues Passwort zu erstellen.</p><p>Bitte tragen Sie die E-Mail Adresse ein, die ihrem Konto zugeordnet ist und übermitteln Sie das Formular.</p>',
    '<p>If you have not asked to create a new password, just do nothing. The link above is valid only for 24 hours and your actual password has not changed now.</p>' =>
         '<p>Falls Sie kein neues Passwort angefordert haben, ignorieren Sie diese E-Mail bitte. Der o.a. Link ist lediglich für 24 Stunden gültig und ihr aktuelles Passwort wurde nicht geändert.</p>',
    'kitFramework - Create new password' =>
        'kitFramework - Neues Passwort anlegen',
    'kitFramework - Login' =>
        'kitFramework - Anmeldung',
    'kitFramework - Logout' =>
        'kitFramework - Abmeldung',
    'kitFramework password reset' =>
        'kitFramework - Passwort zurücksetzen',
    'Link transmitted' =>
        'Link übermittelt',
    'Login' =>
        'Anmelden',
    'Logout' =>
        'Abmelden',
    'Password' =>
        'Passwort',
    '<p>Please login to the kitFramework with your username or email address and the assigned password.</p><p>Your can also use your username and password for the CMS.</p>' =>
        '<p>Bitte melden Sie sich am kitFramework mit Ihrem Benutzernamen oder Ihrer E-Mail Adresse und Ihrem Passwort an.</p><p>Sie können sich auch mit Ihrem Benutzernamen und Passwort für das CMS anmelden.</p>',
    '<p>Please use the following link to create a new password:<br />%reset_url%</p>' =>
        '<p>Bitte verwenden Sie den folgenden Link um ein neues Passwort anzulegen:<br />%reset_url%</p>',
    '<p>Regards<br />Your kitFramework team</p>' =>
        '<p>Mit freundlichn Grüßen<br />Ihr kitFramework Team</p>',
    'Repeat Password' =>
        'Passwort wiederholen',
    '<p>Sorry, but the submitted GUID is invalid.</p><p>Please contact the webmaster.</p>' =>
        '<p>Die übermittelte GUID ist ungültig</p><p>Bitte nehmen Sie mit dem Webmaster Kontakt auf.</p>',
    'Submit' =>
       'Übermitteln',
    '<p>The both passwords you have typed in does not match, please try again!</p>' =>
        '<p>Die beiden Passwörter die Sie eingegeben haben stimmen nicht überein, bitte versuchen Sie es noch einmal!</p>',
    '<p>The password for the kitFramework was successfull changed.</p><p>You can now <a href="%login%">login using the new password</a>.</p>' =>
        '<p>Ihr Passwort für das kitFramework wurde erfolgreich geändert.</p><p>Sie können sich jetzt <a href="%login%">mit Ihrem neuen Passwort anmeldend</a>.</p>',
    '<p>The password you have typed in is not strength enough.</p><p>Please choose a password at minimun 8 characters long, containing lower and uppercase characters, numbers and special chars. Spaces are not allowed.</p>' =>
        '<p>Das übermittelte Passwort ist nicht stark genug.</p><p>Bitte wählen Sie ein Passwort mit mindestens 8 Zeichen Länge, mit einem Mix aus Groß- und Kleinbuchstaben, Zahlen und Sonderzeichen. Leerzeichen sind nicht gestattet.</p>',
    '<p>The submitted GUID was already used and is no longer valid.</p><p>Please <a href="%password_forgotten%">order a new link</a>.</p>' =>
        '<p>Die übermittelte GUID wurde bereits verwendet und ist nicht mehr gültig.</p><p>Bitte <a href="%password_forgotten%">fordern Sie einen neuen Link an</a>.</p>',
    "This link enable you to change your password once within 24 hours." =>
        "Dieser Link ermöglicht es Ihnen, ihr Passwort einmal innerhalb von 24 Stunden zu ändern.",
    '<p>This value is not a valid email address.</p>' =>
        '<p>Es wurde keine gültige E-Mail Adresse übergeben!</p>',
    'Username' =>
        'Benutzername',
    'Username or email address' =>
        'Benutzername oder E-Mail Adresse',
    'We have send a link to your email address %email%.' =>
        'Wir haben Ihnen einen Link an Ihre E-Mail Adresse %email% gesendet.',
    'Welcome' =>
        'Herzlich Willkommen!'
);