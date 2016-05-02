<?php

	namespace N2f\N2fWeb;

	/**
	 * Node for handling N2fWeb configuration.
	 * 
	 * Node for enabling interactive (or non-interactive)
	 * configuration of the N2fWeb extension.
	 * 
	 * @version 1.0
	 * @author Andrew Male
	 * @copyright 2014-2016 Zibings.com
	 * @package N2fWeb
	 */
	class ConfigNode extends \N2f\NodeBase {
		/**
		 * Internal value of base path to config file.
		 * 
		 * @var string
		 */
		protected $BaseConfigPath;

		/**
		 * Instantiates a new ConfigNode object.
		 * 
		 * @return void
		 */
		public function __construct($BaseConfigPath) {
			$this->SetKey('N2fWeb-ConfigNode')->SetVersion('1.0');
			$this->BaseConfigPath = $BaseConfigPath;

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
			if ($Sender === null || !($Sender instanceof \N2f\N2f) || !($Dispatch instanceof \N2f\CliDispatch)) {
				return;
			}

			$Argv = $Dispatch->GetAssocParameters();
			$Ch = $Sender->GetConsoleHelper();
			$Fh = $Sender->GetFileHelper();
			$Jh = $Sender->GetJsonHelper();

			if (!$Ch->IsNaturalCLI()) {
				return;
			}

			if (!array_key_exists('ext', $Argv) || strtolower($Argv['ext']) != 'n2fweb') {
				return;
			}

			$Ch->PutLine("We are ready to configure N2fWeb!");

			$D = array(
				'domain' => '',
				'url_path' => '/',
				'content_type' => 'text/html',
				'core_type' => 'modular',
				'default_start' => 'main',
				'default_error' => 'error',
				'default_skin' => 'default',
				'print_ad' => true,
				'dev_mode' => true
			);

			if ($Fh->FileExists($this->BaseConfigPath)) {
				$Df = $Jh->DecodeAssoc($Fh->GetContents($this->BaseConfigPath));

				foreach ($Df as $Key => $Val) {
					$D[$Key] = $Val;
				}
			}

			$Ch->PutLine();

			if (count($Argv) > 3) {
				foreach ($Argv as $Key => $Val) {
					switch (strtolower($Key)) {
						case 'domain':
							if (!empty(trim($Val))) {
								$D['domain'] = $Val;
								$Ch->PutLine("Site domain set successfully.");
							} else {
								$Ch->PutLine("Site domain must be a non-empty string.");
							}

							break;
						case 'url_path':
							if (!empty(trim($Val))) {
								$D['url_path'] = $Val;
								$Ch->PutLine("Site URL path set successfully.");
							} else {
								$Ch->PutLine("Site URL path must be a non-empty string.");
							}

							break;
						case 'content_type':
							if (!empty(trim($Val))) {
								$D['content_type'] = $Val;
								$Ch->PutLine("Site content type set successfully.");
							} else {
								$Ch->PutLine("Site content type must be a non-empty string.");
							}

							break;
						case 'core_type':
							if (!empty(trim($Val))) {
								if (strtolower(trim($Val)) == 'modular' || strtolower(trim($Val)) == 'mvc') {
									$D['core_type'] = $Val;
									$Ch->PutLine("Site core type set successfully.");
								} else {
									$Ch->PutLine("Site core type must be either 'modular' or 'mvc'.");
								}
							} else {
								$Ch->PutLine("Site core type must be a non-empty string.");
							}

							break;
						case 'default_start':
							if (!empty(trim($Val))) {
								$D['default_start'] = $Val;
								$Ch->PutLine("Site default start set successfully.");
							} else {
								$Ch->PutLine("Site default start must be non-empty string.");
							}

							break;
						case 'default_error':
							if (!empty(trim($Val))) {
								$D['default_error'] = $Val;
								$Ch->PutLine("Site default error set successfully.");
							} else {
								$Ch->PutLine("Site default error must be a non-empty string.");
							}

							break;
						case 'default_skin':
							if (!empty(trim($Val))) {
								$D['default_skin'] = $Val;
								$Ch->PutLine("Site default skin set successfully.");
							} else {
								$Ch->PutLine("Site default skin must be a non-empty string.");
							}

							break;
						case 'print_ad':
							if (!empty(trim($Val))) {
								if (strtolower(trim($Val)) == 'true' || strtolower(trim($Val)) == 'false') {
									$D['print_ad'] = $Val;
									$Ch->PutLine("Site print advert option set successfully.");
								} else {
									$Ch->PutLine("Site print advert option must be either 'true' or 'false'.");
								}
							} else {
								$Ch->PutLine("Site print advert option must be a non-empty string.");
							}

							break;
						case 'dev_mode':
							if (!empty(trim($Val))) {
								if (strtolower(trim($Val)) == 'true' || strtolower(trim($Val)) == 'false') {
									$D['dev_mode'] = $Val;
									$Ch->PutLine("Site developer mode option set successfully.");
								} else {
									$Ch->PutLine("Site developer mode option must be either 'true' or 'false'.");
								}
							} else {
								$Ch->PutLine("Site developer mode option must be a non-empty string.");
							}

							break;
						default:
							break;
					}
				}
			} else {
				$Ch->PutLine("Answer the following prompts to create/update your N2fWeb instance configuration.");
				$Ch->PutLine();

				$CfgDomain = $Ch->GetQueriedInput(
					"Site Domain",
					$D['domain'],
					"Invalid domain value."
				);

				if ($CfgDomain->IsBad()) {
					$Ch->PutLine("Max attempt failed, aborting N2fWeb configuration entirely.");

					return;
				}

				$D['domain'] = $CfgDomain->GetResults();

				$CfgUrlPath = $Ch->GetQueriedInput(
					"Site URL Path",
					$D['url_path'],
					"Invalid URL path value."
				);

				if ($CfgUrlPath->IsBad()) {
					$Ch->PutLine("Max attempt failed, aborting N2fWeb configuration entirely.");

					return;
				}

				$D['url_path'] = $CfgUrlPath->GetResults();

				$CfgContentType = $Ch->GetQueriedInput(
					"Site Content-Type",
					$D['content_type'],
					"Invalid content-type value."
				);

				if ($CfgContentType->IsBad()) {
					$Ch->PutLine("Max attempt failed, aborting N2fWeb configuration entirely.");

					return;
				}

				$D['content_type'] = $CfgContentType->GetResults();

				$CfgCoreType = $Ch->GetQueriedInput(
					"Site Core Type",
					$D['core_type'],
					"Invalid core type value.",
					5,
					function ($Value) { return !empty(trim($Value)) && (strtolower(trim($Value)) == 'modular' || strtolower(trim($Value)) == 'mvc'); },
					function ($Value) { return strtolower(trim($Value)); }
				);

				if ($CfgCoreType->IsBad()) {
					$Ch->PutLine("Max attempt failed, aborting N2fWeb configuration entirely.");

					return;
				}

				$D['core_type'] = $CfgCoreType->GetResults();

				$CfgDefaultStart = $Ch->GetQueriedInput(
					"Site Default Start",
					$D['default_start'],
					"Invalid default start value."
				);

				if ($CfgDefaultStart->IsBad()) {
					$Ch->PutLine("Max attempt failed, aborting N2fWeb configuration entirely.");

					return;
				}

				$D['default_start'] = $CfgDefaultStart->GetResults();

				$CfgDefaultError = $Ch->GetQueriedInput(
					"Site Default Error",
					$D['default_error'],
					"Invalid default error value."
				);

				if ($CfgDefaultError->IsBad()) {
					$Ch->PutLine("Max attempt failed, aborting N2fWeb configuration entirely.");

					return;
				}

				$D['default_error'] = $CfgDefaultError->GetResults();

				$CfgDefaultSkin = $Ch->GetQueriedInput(
					"Site Default Skin",
					$D['default_skin'],
					"Invalid default skin value."
				);

				if ($CfgDefaultSkin->IsBad()) {
					$Ch->PutLine("Max attempt failed, aborting N2fWeb configuration entirely.");

					return;
				}

				$D['default_skin'] = $CfgDefaultSkin->GetResults();

				$adDef = ($D['print_ad']) ? 'Y/n' : 'y/N';
				$CfgPrintAd = $Ch->GetQueriedInput(
					"N2f Print Advert Toggle",
					$adDef,
					"Invalid print advert toggle value.",
					5,
					function ($Value) { return !empty(trim($Value)) && (strtolower(trim($Value)) == 'y' || strtolower(trim($Value)) == 'n'); },
					function ($Value) { return strtolower(trim($Value)); }
				);

				if ($CfgPrintAd->IsBad()) {
					$Ch->PutLine("Max attempt failed, aborting N2fWeb configuration entirely.");

					return;
				}

				$adTmp = $CfgPrintAd->GetResults();

				if ($adTmp != $adDef) {
					$D['print_ad'] = ($adTmp == 'y') ? true : false;
				}

				$dmDef = ($D['dev_mode']) ? 'Y/n' : 'y/N';
				$CfgDevMode = $Ch->GetQueriedInput(
					"N2f Developer Mode Toggle",
					$dmDef,
					"Invalid developer mode toggle value.",
					5,
					function ($Value) { return !empty(trim($Value)) && (strtolower(trim($Value)) == 'y' || strtolower(trim($Value)) == 'n'); },
					function ($Value) { return strtolower(trim($Value)); }
				);

				if ($CfgDevMode->IsBad()) {
					$Ch->PutLine("Max attempt failed, aborting N2fWeb configuration entirely.");

					return;
				}

				$dmTmp = $CfgDevMode->GetResults();

				if ($dmTmp != $dmDef) {
					$D['dev_mode'] = ($dmTmp == 'y') ? true : false;
				}
			}

			$Fh->PutContents($this->BaseConfigPath, $Jh->EncodePretty($D));

			$Ch->PutLine();
			$Ch->PutLine("N2fWeb has been successfully configured.");

			$Dispatch->Consume();

			return;
		}
	}