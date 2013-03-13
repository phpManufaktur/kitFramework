<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use phpManufaktur\Basic\Control\gitHub\gitHub;
use phpManufaktur\Basic\Control\unZip\unZip;

class Setup {

	protected $app;

	public function __construct() {
		global $app;
		$this->app = $app;
		if (!function_exists('curl_init'))	{
		    throw new \Exception('Need the installed and enabled cURL extension!');
		}
	} // __construct()

	/**
	 * Download a file with cURL
	 *
	 * @param string $source_url
	 * @param string $target_path
	 * @throws \Exception
	 */
	protected function curlDownload($source_url, $target_path, &$info=array()) {
	    try {
	        // init cURL
	        $ch = curl_init();
	        // set the cURL options
	        curl_setopt($ch,CURLOPT_URL, $source_url);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_FAILONERROR, true);
	        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
	        // exec cURL and get the file content
	        if (false === ($file_content = curl_exec($ch))) {
	            throw new \Exception(sprintf('cURL Error: [%d] - %s', curl_errno($ch), curl_error($ch)));
	        }
	        if (!curl_errno($ch)) {
	            $info = curl_getinfo($ch);
	        }
	        // close the connection
	        curl_close($ch);

	        if (isset($info['http_code']) && ($info['http_code'] != '200'))
	            return false;

	        // create the target file
	        if (false === ($downloaded_file = fopen($target_path, 'w')))
	            throw new \Exception('fopen() fails!');
	        // write the content to the target file
	        if (false === ($bytes = fwrite($downloaded_file, $file_content)))
	            throw new \Exception('fwrite() fails!');
	        // close the target file
	        fclose($downloaded_file);
	    } catch (\Exception $e) {
	        throw new \Exception($e->getMessage());
	    }
	} // curlDownload()

	public function dialogStart() {
		return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'start.setup.twig'),
		    array(

		        ));
	} // showDialog()

	public function startSetup() {
	    // init GitHub
	    $gitHub = new gitHub();
	    $extension_version = null;
	    // get the last kfExtension release
	    if (false === ($extension_url = $gitHub->getLastRepositoryZipUrl('phpManufaktur', 'kfExtension', $extension_version))) {
	        throw new \Exception('Can\'t get the download URL for the last kfExtension repository!');
	    }

	    // download kfExtension
	    $info = array();
	    $zip_target_path = FRAMEWORK_TEMP_PATH.'/kfExtension.zip';
	    if (!$this->curlDownload($extension_url, $zip_target_path, $info)) {
	        if (isset($info['http_code']) && ($info['http_code'] == '302') &&
	        isset($info['redirect_url']) && !empty($info['redirect_url'])) {
	            // follow the redirect URL!
	            $redirect_url = $info['redirect_url'];
	            $info = array();
	            $this->curlDownload($redirect_url, $zip_target_path, $info);
	        }
	        elseif (isset($info['http_code']) && ($info['http_code'] != '200')) {
	            throw new \Exception(sprintf('[GitHub Error] HTTP Code: %s - no further informations available!', $info['http_code']));
	        }
	    }

	    // unzip kfExtension to the target path
	    $unzip = new unZip();

	    // create target directory
	    $temp_path = FRAMEWORK_TEMP_PATH.'/unzip/kfExtension';
	    $unzip->checkDirectory($temp_path);
	    $unzip->setUnZipPath($temp_path);

	    $unzip->extract($zip_target_path);

	    // GitHub ZIP's contain a subdirectory with name we don't know ...
	    $source_dir = '';

	    $handle = opendir($temp_path);
	    // we loop through the temp dir to get the first subdirectory ...
	    while (false !== ($file = readdir($handle))) {
	        if ('.' == $file || '..' == $file) continue;
	        if (is_dir($temp_path.'/'.$file)) {
	            // ... here we got it!
	            $source_dir = $temp_path.'/'.$file;
	            break;
	        }
	    }

	    if ($source_dir == '')
	        throw new \Exception('The unzipped archive has an unexpected structure, please contact the support!');

	    return "ok - $source_dir";

	    return 'start: '.$extension_url;
	}

} // class Account