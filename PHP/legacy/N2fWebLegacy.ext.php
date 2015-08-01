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
								$Ch->PutLine("Copied '" . $Res . "'");
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
								$Ch->PutLine("Copied '" . $Res . "'");
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

			/* @var \N2f\N2f $Sender */

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
				/* @var \N2f\CliDispatch $Dispatch */

				$Sender->GetConsoleHelper()->PutLine();
				$Sender->GetConsoleHelper()->PutLine("CLI mode not supported with n2f-web legacy.");
				$Sender->GetConsoleHelper()->PutLine();

				$Dispatch->Consume();

				return;
			} else {
				$Sender->GetConsoleHelper()->PutLine();
				$Sender->GetConsoleHelper()->PutLine("Invalid dispatch provided for n2f-web legacy.");
				$Sender->GetConsoleHelper()->PutLine();

				$Dispatch->Consume();

				return;
			}

			$Dispatch->Consume();

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



			return;
		}
	}

?>