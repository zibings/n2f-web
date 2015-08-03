<?php

	class N2fWebLegacy extends N2f\ExtensionBase {
		public function Initialize(N2f\N2f &$N2f) {
			$N2f->LinkConfigNode(new N2fWebLegacyConfigNode());
			$N2f->LinkExecuteNode(new N2fWebLegacyExecuteNode());
			$N2f->LinkGenerationNode(new N2fWebLegacyGenerateNode());

			return;
		}
	}

	class N2fWebLegacyConfigNode extends N2f\NodeBase {
		public function __construct() {
			$this->SetKey('N2fWebLegacyConfigNode')->SetVersion('1.0');

			return;
		}

		public function Process($Sender, N2f\DispatchBase &$Dispatch) {
			if (!($Dispatch instanceof N2f\ConfigDispatch) || !($Sender instanceof N2f\N2f)) {
				return;
			}

			/* @var \N2f\N2f $Sender */
			/* @var \N2f\ConfigDispatch $Dispatch */

			$Cfg = $Sender->GetConfig();
			$Ch = $Sender->GetConsoleHelper();
			$Fh = $Sender->GetFileHelper();

			if ($Dispatch->GetExt() != 'N2fWebLegacy') {
				return;
			}

			$DoVerbose = false;

			if ($Ch->HasArg('v') || $Ch->HasArg('verbose')) {
				$DoVerbose = true;
			}

			if (!$Fh->FolderExists("~modules/")) {
				$Ch->PutLine();
				$Ch->PutLine("Configuring n2f-web legacy extension");
				$Ch->PutLine();

				$Ch->Put("Installing 'modules' directory... ");

				$ModCopy = $Fh->CopyFolder($Cfg->ExtensionDirectory . "N2fWebLegacy/modules/", "~modules/");

				if ($ModCopy->IsGud()) {
					if ($DoVerbose) {
						$Results = $ModCopy->GetResults();
						$Ch->PutLine();

						if (is_array($Results)) {
							foreach (array_values($Results) as $Res) {
								$Ch->PutLine("Created '" . $Res . "'");
							}
						} else {
							$Ch->PutLine("Created '" . $Results . "'");
						}
					}

					$Ch->PutLine("Complete.");
				} else {
					if ($DoVerbose) {
						$Ch->PutLine();

						foreach (array_values($ModCopy->GetMessages()) as $Msg) {
							$Ch->PutLine($Msg);
						}
					}

					$Ch->PutLine("Failed.");
					$Ch->PutLine();
					$Ch->PutLine("Aborting configuration.");
					$Ch->PutLine();

					$Dispatch->Consume();

					return;
				}

				$Ch->Put("Installing 'resources' directory... ");

				$ResCopy = $Fh->CopyFolder($Cfg->ExtensionDirectory . "N2fWebLegacy/resources/", "~resources/");

				if ($ResCopy->IsGud()) {
					if ($DoVerbose) {
						$Results = $ResCopy->GetResults();
						$Ch->PutLine();

						if (is_array($Results)) {
							foreach (array_values($Results) as $Res) {
								$Ch->PutLine("Created '" . $Res . "'");
							}
						} else {
							$Ch->PutLine("Created '" . $Results . "'");
						}
					}

					$Ch->PutLine("Complete.");
				} else {
					if ($DoVerbose) {
						$Ch->PutLine();

						foreach (array_values($ResCopy->GetMessages()) as $Msg) {
							$Ch->PutLine($Msg);
						}
					}

					$Ch->PutLine("Failed.");
					$Ch->PutLine();
					$Ch->PutLine("Aborting configuration.");
					$Ch->PutLine();

					$Dispatch->Consume();

					return;
				}
			} else {
				$Ch->PutLine();
				$Ch->PutLine("The n2f-web legacy extension has already been configured.");
				$Ch->PutLine();
			}

			return;
		}
	}

	class N2fWebLegacyExecuteNode extends N2f\NodeBase {
		public function __construct() {
			$this->SetKey('N2fWebLegacyExecuteNode')->SetVersion('1.0');

			return;
		}

		public function Process($Sender, N2f\DispatchBase &$Dispatch) {
			if (!($Sender instanceof N2f\N2f)) {
				return;
			}

			/** @var \N2f\N2f $Sender */

			if (!$Sender->GetFileHelper()->FolderExists("~/modules/")) {
				$Sender->GetConsoleHelper()->PutLine();
				$Sender->GetConsoleHelper()->PutLine("The n2f-web legacy extension has not been configured, please run 'n2f-cli config N2fWebLegacy' before running.");
				$Sender->GetConsoleHelper()->PutLine();

				$Dispatch->Consume();

				return;
			}

			if ($Dispatch instanceof N2f\JsonDispatch) {
				$_REQUEST['nret'] = 'data';
			} else if ($Dispatch instanceof N2f\CliDispatch) {
				/** @var \N2f\CliDispatch $Dispatch */

				$Sender->GetConsoleHelper()->PutLine();
				$Sender->GetConsoleHelper()->PutLine("CLI mode not supported with n2f-web legacy.");
				$Sender->GetConsoleHelper()->PutLine();

				$Dispatch->Consume();

				return;
			} else if (!($Dispatch instanceof N2f\WebDispatch)) {
				$Sender->GetConsoleHelper()->PutLine();
				$Sender->GetConsoleHelper()->PutLine("Invalid dispatch provided for n2f-web legacy.");
				$Sender->GetConsoleHelper()->PutLine();

				$Dispatch->Consume();

				return;
			}

			$Dispatch->Consume();

			$ExtDir = $Sender->GetConfig()->ExtensionDirectory;

			if (substr($ExtDir, 0, 1) == '~') {
				$ExtDir = substr($ExtDir, 1);

				if (substr($ExtDir, 0, 1) == '/' && substr(N2F_REL_DIR, -1) == '/') {
					$ExtDir = substr($ExtDir, 1);
				}
			}

			define('N2F_REL_PATH', N2F_REL_DIR . $ExtDir . 'N2fWebLegacy/');

			require(N2F_REL_PATH . 'core.inc.php');
			n2f_proc();

			return;
		}
	}

	class N2fWebLegacyGenerateNode extends N2f\NodeBase {
		public function __construct() {
			$this->SetKey('N2fWebLegacyGenerateNode')->SetVersion('1.0');

			return;
		}

		public function Process($Sender, N2f\DispatchBase &$Dispatch) {
			if (!($Dispatch instanceof N2f\GenerateDispatch) || !($Sender instanceof N2f\N2f)) {
				return;
			}

			/** @var \N2f\N2f $Sender */
			/** @var \N2f\GenerateDispatch $Dispatch */

			$Type = $Dispatch->GetEntityType();
			$Params = $Dispatch->GetAssocParameters();

			if (stripos($Type, 'n2fweblegacy:') === false || !array_key_exists('name', $Params)) {
				return;
			}

			$DoVerbose = false;
			$Cfg = $Sender->GetConfig();
			$Fh = $Dispatch->GetFileHelper();
			$Ch = $Sender->GetConsoleHelper();

			$Ch->PutLine();
			$Dispatch->Consume();
			$Type = substr($Type, 13);

			if ($Ch->HasArg('v') || $Ch->HasArg('verbose')) {
				$DoVerbose = true;
			}

			switch ($Type) {
				case 'extension':
					if (!array_key_exists('key', $Params)) {
						while (true) {
							$Ch->Put("Enter the extension key: ");
							$Key = $Ch->GetLine();

							if (!empty($Key) && stripos($Key, ' ') === false) {
								$Params['key'] = $Key;

								break;
							} else {
								$Ch->PutLine("You must enter a valid extension key without spaces.");
							}
						}
					}

					if (!array_key_exists('version', $Params)) {
						while (true) {
							$Ch->Put("Enter extension version: ");
							$Ver = $Ch->GetLine();

							if (!empty($Ver)) {
								$Params['version'] = $Ver;

								break;
							} else {
								$Ch->PutLine("You must enter a valid extension version.");
							}
						}
					}

					if (!array_key_exists('author', $Params)) {
						while (true) {
							$Ch->Put("Enter extension author(s): ");
							$Author = $Ch->GetLine();

							if (!empty($Author)) {
								$Params['author'] = $Author;

								break;
							} else {
								$Ch->PutLine("You must enter a valid extension author.");
							}
						}
					}

					if (!array_key_exists('url', $Params)) {
						while (true) {
							$Ch->Put("Enter extension URL: ");
							$Url = $Ch->GetLine();

							if (!empty($Url)) {
								$Params['url'] = $Url;

								break;
							} else {
								$Ch->PutLine("You must enter a valid extension URL.");
							}
						}
					}

					$Ch->PutLine();
					$Ch->Put("Creating extension.. ");
					$ExtCopy = $Fh->CopyFile($Cfg->ExtensionDirectory . 'N2fWebLegacy/templates/extension/extension.ext.php', $Cfg->ExtensionDirectory . 'N2fWebLegacy/extensions/' . $Params['name'] . '.ext.php');

					if ($ExtCopy->IsGud()) {
						if ($DoVerbose) {
							$Results = $ExtCopy->GetResults();
							$Ch->PutLine();

							if (is_array($Results)) {
								foreach (array_values($Results) as $Res) {
									$Ch->PutLine("Created '" . $Res . "'");
								}
							} else {
								$Ch->PutLine("Created '" . $Results . "'");
							}
						}

						$Contents = $Fh->GetContents($Cfg->ExtensionDirectory . 'N2fWebLegacy/extensions/' . $Params['name'] . '.ext.php');
						$Contents = str_replace(
							array(
								'%EXT_KEY%',
								'%EXT_NAME%',
								'%EXT_VERSION%',
								'%EXT_AUTHOR%',
								'%EXT_URL%'
							),
							array(
								$Params['key'],
								$Params['name'],
								$Params['version'],
								$Params['author'],
								$Params['url']
							),
							$Contents);
						
						$ExtWrite = $Fh->PutContents($Cfg->ExtensionDirectory . 'N2fWebLegacy/extensions/' . $Params['name'] . '.ext.php', $Contents);

						if ($ExtWrite->IsGud()) {
							$Ch->PutLine("Complete.");
							$Ch->PutLine();
							$Ch->PutLine("The '{$Params['name']}' extension must still be configured for use inside the config.inc.php file.");
							$Ch->PutLine();
						} else {
							if ($DoVerbose) {
								$Ch->PutLine();

								foreach (array_values($ExtWrite->GetMessages()) as $Msg) {
									$Ch->PutLine($Msg);
								}
							}

							$Ch->PutLine("Failed.");
							$Ch->PutLine();
							$Ch->PutLine("Extension file created but configuration information could not be written.");
							$Ch->PutLine();
						}
					} else {
						if ($DoVerbose) {
							$Ch->PutLine();

							foreach (array_values($ExtCopy->GetMessages()) as $Msg) {
								$Ch->PutLine($Msg);
							}
						}

						$Ch->PutLine("Failed.");
						$Ch->PutLine();
						$Ch->PutLine("Aborting configuration.");
						$Ch->PutLine();
					}

					break;
				case 'module':
					$Ch->Put("Creating '{$Params['name']}' module.. ");

					$ModCopy = $Fh->CopyFolder($Cfg->ExtensionDirectory . 'N2fWebLegacy/templates/module/', "~modules/{$Params['name']}/");

					if ($ModCopy->IsGud()) {
						if ($DoVerbose) {
							$Results = $ModCopy->GetResults();
							$Ch->PutLine();

							if (is_array($Results)) {
								foreach (array_values($Results) as $Res) {
									$Ch->PutLine("Created '" . $Res . "'");
								}
							} else {
								$Ch->PutLine("Created '" . $Results . "'");
							}
						}

						$PageContents = str_replace('%MODULE_NAME%', $Params['name'], $Fh->GetContents("~modules/{$Params['name']}/page.php"));
						$TplContents = str_replace('%MODULE_NAME%', $Params['name'], $Fh->GetContents("~modules/{$Params['name']}/tpl/default/index.tpl"));

						$PageWrite = $Fh->PutContents("~modules/{$Params['name']}/page.php", $PageContents);
						$TplWrite = $Fh->PutContents("~modules/{$Params['name']}/tpl/default/index.tpl", $TplContents);

						if ($PageWrite->IsGud() && $TplWrite->IsGud()) {
							if ($DoVerbose) {
								$Results = $PageWrite->GetResults();
								$Ch->PutLine();

								if (is_array($Results)) {
									foreach (array_values($Results) as $Res) {
										$Ch->PutLine("Wrote '" . $Res . "' bytes to disk");
									}
								} else {
									$Ch->PutLine("Wrote '" . $Results . "' bytes to disk");
								}

								$Results = $TplWrite->GetResults();

								if (is_array($Results)) {
									foreach (array_values($Results) as $Res) {
										$Ch->PutLine("Wrote '" . $Res . "' bytes to disk");
									}
								} else {
									$Ch->PutLine("Wrote '" . $Results . "' bytes to disk");
								}
							}

							$Ch->PutLine("Complete.");
						} else {
							if ($DoVerbose) {
								$Ch->PutLine();

								foreach (array_values($PageWrite->GetMessages()) as $Msg) {
									$Ch->PutLine($Msg);
								}

								foreach (array_values($TplWrite->GetMessages()) as $Msg) {
									$Ch->PutLine($Msg);
								}
							}

							$Ch->PutLine("Failed.");
							$Ch->PutLine();
							$Ch->PutLine("Module files were copied, but configuration information could not be written.");
							$Ch->PutLine();
						}
					} else {
						if ($DoVerbose) {
							$Ch->PutLine();

							foreach (array_values($ModCopy->GetMessages()) as $Msg) {
								$Ch->PutLine($Msg);
							}
						}

						$Ch->PutLine("Failed.");
						$Ch->PutLine();
						$Ch->PutLine("Aborting configuration.");
						$Ch->PutLine();
					}

					break;
				default:
					$Ch->PutLine("Invalid generation type for n2f-web legacy extension.");
					$Ch->PutLine();

					return;
			}

			return;
		}
	}

?>