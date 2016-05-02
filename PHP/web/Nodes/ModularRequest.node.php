<?php

	namespace N2f\N2fWeb;

	/**
	 * Node for handling modular web requests.
	 * 
	 * Node for handling web requests using the
	 * modular pattern.
	 * 
	 * @version 1.0
	 * @author Andrew Male
	 * @copyright 2014-2016 Zibings.com
	 * @package N2fWeb
	 */
	class ModularRequestNode extends \N2f\NodeBase {
		/**
		 * Internal instance of N2fWeb config.
		 * 
		 * @var \N2f\N2fWeb\Config
		 */
		protected $Config;

		/**
		 * Instantiates a ModularRequestNode object.
		 * 
		 * @param \N2f\N2fWeb\Config $Config Value for existing \N2f\N2fWeb\Config instance.
		 * @return void
		 */
		public function __construct(Config $Config) {
			$this->Config = $Config;
			$this->SetKey("n2fweb-modularrequestnode")->SetVersion("1.0");

			return;
		}

		/**
		 * Method to process a dispatch from a sender.
		 *
		 * @param mixed $Sender Object (or other) that initiated chain traversal.
		 * @param \N2f\DispatchBase $Dispatch Dispatch to process from sender.
		 * @return void
		 */
		public function Process($Sender, \N2f\DispatchBase &$Dispatch) {
			if ($Sender === null || !($Sender instanceof \N2f\N2f) || !($Dispatch instanceof \N2f\WebDispatch) || !($Dispatch instanceof \N2f\JsonWebDispatch)) {
				return;
			}

			// Thoughts:
			//  - No more sys.ext.php files, they weren't module-specific
			//  - In devmode, should look for module paths somehow in all N2f loaded extensions
			//  - Loaded via the N2fWeb class node registration
			//  - Maybe through in a class that behaves similar to the old n2f.cls.php, but without the extensions?

			return;
		}
	}