<?php

	namespace N2f\N2fWeb;

	class CoreTypes extends \N2f\Enum {
		const Modular = 1;
		const Mvc = 2;
	}

	/**
	 * Configuration class for N2fWeb.
	 * 
	 * Class that represents all configuration
	 * values for the N2fWeb base system.
	 * 
	 * @version 1.0
	 * @author Andrew Male
	 * @copyright 2014-2016 Zibings.com
	 * @package N2fWeb
	 */
	class Config {
		/**
		 * Value for the site's domain.
		 * 
		 * @var string
		 */
		public $Domain;
		/**
		 * Value for the site's url path.
		 * 
		 * @var string
		 */
		public $UrlPath;
		/**
		 * Value for the content-type to
		 * send in HTTP headers.
		 * 
		 * @var string
		 */
		public $ContentType;
		/**
		 * Value of the type of core to
		 * use for the file system.
		 * 
		 * @var CoreTypes
		 */
		public $CoreType;
		/**
		 * Value of default entry point
		 * for site.
		 * 
		 * @var string
		 */
		public $DefaultStart;
		/**
		 * Value of default error point
		 * for site.
		 * 
		 * @var string
		 */
		public $DefaultError;
		/**
		 * Value of default skin name.
		 * 
		 * @var string
		 */
		public $DefaultSkin;
		/**
		 * Value of toggle for printing
		 * unintrusive N2f2 advert.
		 * 
		 * @var bool
		 */
		public $PrintAd;
		/**
		 * Value of toggle for developer
		 * mode.
		 * 
		 * @var bool
		 */
		public $DevMode;

		/**
		 * Instantiates a new Config class.
		 * 
		 * If provided, config values are loaded from the
		 * file indicated by $ConfigPath.  Optionally, both
		 * the FileHelper and JsonHelper can be overridden
		 * with injected instances, otherwise both are
		 * created with default values.
		 * 
		 * @param string $ConfigPath String value of config file path (can have ~ for FileHelper root processing).
		 * @param \N2f\FileHelper $FileHelper Optional existing \N2f\FileHelper instance.
		 * @param \N2f\JsonHelper $JsonHelper Optional existing \N2f\JsonHelper instance.
		 * @return void
		 */
		public function __construct($ConfigPath = null, \N2f\FileHelper $FileHelper = null, \N2f\JsonHelper $JsonHelper = null) {
			$this->Initialize();

			if ($ConfigPath === null) {
				return;
			}

			if ($FileHelper === null) {
				$FileHelper = new \N2f\FileHelper();
			}

			if ($JsonHelper === null) {
				$JsonHelper = new \N2f\JsonHelper();
			}

			if (!$FileHelper->FileExists($ConfigPath)) {
				return;
			}

			$Cfg = $JsonHelper->DecodeAssoc($FileHelper->GetContents($ConfigPath));

			if (count($Cfg) < 1) {
				return;
			}

			$this->Domain = (array_key_exists('domain', $Cfg)) ? $Cfg['domain'] : '';
			$this->UrlPath = (array_key_exists('url_path', $Cfg)) ? $Cfg['url_path'] : '/';
			$this->ContentType = (array_key_exists('content_type', $Cfg)) ? $Cfg['content_type'] : 'text/html';
			$this->DefaultStart = (array_key_exists('default_start', $Cfg)) ? $Cfg['default_start'] : 'main';
			$this->DefaultError = (array_key_exists('default_error', $Cfg)) ? $Cfg['default_error'] : 'error';
			$this->DefaultSkin = (array_key_exists('default_skin', $Cfg)) ? $Cfg['default_skin'] : 'default';

			if (array_key_exists('core_type', $Cfg)) {
				$this->CoreType = (strtolower($Cfg['core_type']) == 'modular') ? new CoreTypes(CoreTypes::Modular) : new CoreTypes(CoreTypes::Mvc);
			} else {
				$this->CoreType = new CoreTypes(CoreTypes::Modular);
			}

			if (array_key_exists('print_ad', $Cfg)) {
				$this->PrintAd = (strtolower($Cfg['print_ad']) == 'true') ? true : false;
			} else {
				$this->PrintAd = true;
			}

			if (array_key_exists('dev_mode', $Cfg)) {
				$this->DevMode = (strtolower($Cfg['dev_mode']) == 'true') ? true : false;
			} else {
				$this->DevMode = true;
			}

			return;
		}

		/**
		 * Internal method to set all values to appropriate
		 * defaults.
		 * 
		 * @return void
		 */
		protected function Initialize() {
			$this->Domain = '';
			$this->UrlPath = '/';
			$this->ContentType = 'text/html';
			$this->CoreType = new CoreTypes(CoreTypes::Modular);
			$this->DefaultStart = 'main';
			$this->DefaultError = 'error';
			$this->DefaultSkin = 'default';
			$this->PrintAd = true;
			$this->DevMode = true;

			return;
		}

		/**
		 * Method to save the config values out to a file in
		 * JSON format.
		 * 
		 * @param string $ConfigPath String value of config path.
		 * @param \N2f\FileHelper $FileHelper Optional existing \N2f\FileHelper instance.
		 * @param \N2f\JsonHelper $JsonHelper Optional existing \N2f\JsonHelper instance.
		 * @return void
		 */
		public function Save($ConfigPath, \N2f\FileHelper $FileHelper = null, \N2f\JsonHelper $JsonHelper = null) {
			if (empty($ConfigPath)) {
				return;
			}

			if ($FileHelper === null) {
				$FileHelper = new \N2f\FileHelper();
			}

			if ($JsonHelper === null) {
				$JsonHelper = new \N2f\JsonHelper();
			}

			$CfgVals = array(
				'domain' => $this->Domain,
				'url_path' => $this->UrlPath,
				'content_type' => $this->ContentType,
				'core_type' => ($this->CoreType == new CoreTypes(CoreTypes::Modular)) ? 'modular' : 'mvc',
				'default_start' => $this->DefaultStart,
				'default_error' => $this->DefaultError,
				'default_skin' => $this->DefaultSkin,
				'print_ad' => $this->PrintAd,
				'dev_mode' => $this->DevMode
			);

			$FileHelper->PutContents($ConfigPath, $JsonHelper->EncodePretty($CfgVals));

			return;
		}
	}
