<?php namespace AmitKhare\PHPInstaller;

class Installer {
	private $baseDir;

	function __construct($baseDir){
		$this->baseDir = $baseDir;
	}

	private function returnHtaccessRoot(){
		return "<IfModule mod_rewrite.c>
				    RewriteEngine on
				    RewriteRule    ^$   public/    [L]
				    RewriteRule    (.*) public/$1  [L]
				</IfModule>";
	}

	private function returnHtaccessPublic(){
		return "RewriteEngine On
				# Some hosts may require you to use the `RewriteBase` directive.
				# If you need to use the `RewriteBase` directive, it should be the
				# absolute physical path to the directory that contains this htaccess file.
				#
				# RewriteBase /

				RewriteCond %{REQUEST_FILENAME} !-f
				RewriteRule ^ index.php [QSA,L]";
	}

	private function createFile($path=__DIR__,$name="file.ext",$content=""){
		$filepath = $path.DIRECTORY_SEPARATOR.$name;
		if($this->createDir($path)){
			$f = fopen($filepath, "c");
			fwrite($f, $content);
			fclose($f);
		}
	}

	private function deleteSelf() {
	    unlink(__FILE__);
	}

	private function createHtaccess($path=__DIR__,$content=""){
		if($this->createFile($path,".htaccess",$content)){
			return true;
		}
	}

	private function createDir($path=__DIR__){
		if(!file_exists($path)){
			if(mkdir($path)){
				return true;
			}
		}
	}
}
