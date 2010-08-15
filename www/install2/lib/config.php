<?php
class Config {
	public $DB_TYPE;
	public $DB_HOST;
	public $DB_USER;
	public $DB_PASSWORD;
	public $DB_NAME;
	
	public $NNTP_USERNAME;
	public $NNTP_PASSWORD;
	public $NNTP_SERVER;
	public $NNTP_PORT;
	
	public $GOOGLE_ADSENSE_ACC;
	
	public $WWW_DIR;
	public $SMARTY_DIR;
	public $DB_DIR;
	public $MISC_DIR;
	
	public $ADMIN_USER;
	public $ADMIN_PASS;
	public $ADMIN_EMAIL;
	
	public $doCheck = false;
	
	public $sha1Check;
	public $mysqlCheck;
	public $cacheCheck;
	public $coversCheck;
	public $configCheck;
	public $pearCheck;
	public $schemaCheck;
	
	public $dbConnCheck;
	public $dbNameCheck;
	
	public $nntpCheck;
	
	public $adminCheck;
	
	public $error = false;
	
	function Config() {
		$this->WWW_DIR = dirname(realpath('.'));
		$this->SMARTY_DIR = $this->WWW_DIR.'/lib/smarty';
		$this->DB_DIR = dirname(realpath('..')).'/db';
		$this->MISC_DIR = dirname(realpath('..')).'/misc';
	}
	
	public function setSession() {
		$_SESSION['cfg'] = serialize($this);
	}
	
	public function getSession() {
		$tmpCfg = unserialize($_SESSION['cfg']);
		$tmpCfg->error = false;
		$tmpCfg->doCheck = false;
		return $tmpCfg;
	}
	
	public function isInitialized() {
		return (isset($_SESSION['cfg']) && is_object(unserialize($_SESSION['cfg'])));
	}
	
	public function setConfig($tmpCfg) {
		preg_match_all('/define\((.*?)\)/i', $tmpCfg, $matches);
		$defines = $matches[1];
		foreach ($defines as $define) {
			$define = str_replace('\'', '', $define);
			list($defName,$defVal) = explode(',', $define);
			switch($defName) {
				case 'INSTALL_CHECK':
				case 'WWW_DIR':
				case 'SMARTY_DIR':
				break;
				default:
					$this->{$defName} = trim($defVal);
				break;
			}
		}
	}
	
	public function saveConfig() {
		$tmpCfg = file_get_contents($this->WWW_DIR.'/install2/config.php.tpl');
		$tmpCfg = str_replace('%%DB_HOST%%', $this->DB_HOST, $tmpCfg);
		$tmpCfg = str_replace('%%DB_USER%%', $this->DB_USER, $tmpCfg);
		$tmpCfg = str_replace('%%DB_PASSWORD%%', $this->DB_PASSWORD, $tmpCfg);
		$tmpCfg = str_replace('%%DB_NAME%%', $this->DB_NAME, $tmpCfg);
		
		$tmpCfg = str_replace('%%NNTP_USERNAME%%', $this->NNTP_USERNAME, $tmpCfg);
		$tmpCfg = str_replace('%%NNTP_PASSWORD%%', $this->NNTP_PASSWORD, $tmpCfg);
		$tmpCfg = str_replace('%%NNTP_SERVER%%', $this->NNTP_SERVER, $tmpCfg);
		$tmpCfg = str_replace('%%NNTP_PORT%%', $this->NNTP_PORT, $tmpCfg);
		
		return file_put_contents($this->WWW_DIR.'/config.php', $tmpCfg);
	}
}