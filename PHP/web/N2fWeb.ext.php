<?php

use \N2f\N2fWeb as N2W;

	class N2fWeb extends N2f\ExtensionBase {
		public function Initialize(N2f\N2f &$N2f, N2f\Extension &$Extension) {
			if ($N2f === null) {
				return;
			}

			$Fh = $N2f->GetFileHelper();
			$BasePath = $Extension->GetBasePath();
			$BaseConfigPath = "{$BasePath}N2fWeb.Web.cfg";

			// Misc
			$Fh->Load("{$BasePath}Config.cls.php");

			// Dispatches
			$Fh->Load("{$BasePath}Dispatches/ModularWebRequest.dispatch.php");
			$Fh->Load("{$BasePath}Dispatches/WebCompile.dispatch.php");
			$Fh->Load("{$BasePath}Dispathces/WebConfig.dispatch.php");

			// Helpers
			$Fh->Load("{$BasePath}Helpers/DatabaseHelper.cls.php");
			$Fh->Load("{$BasePath}Helpers/PaginateHelper.cls.php");
			$Fh->Load("{$BasePath}Helpers/SessionHelper.cls.php");
			$Fh->Load("{$BasePath}Helpers/TemplateHelper.cls.php");

			// Nodes
			$Fh->Load("{$BasePath}Nodes/Compile.node.php");
			$Fh->Load("{$BasePath}Nodes/Config.node.php");
			$Fh->Load("{$BasePath}Nodes/Generate.node.php");
			$Fh->Load("{$BasePath}Nodes/ModularRequest.node.php");

			// Register config node, always need this
			$N2f->LinkConfigNode(new N2W\ConfigNode($BaseConfigPath));

			if ($Fh->FileExists($BaseConfigPath)) {
				$Cfg = new N2W\Config($BaseConfigPath, $Fh, $N2f->GetJsonHelper());

				$N2f->LinkGenerationNode(new N2W\GenerateNode());
				$N2f->LinkExecuteNode(new N2W\CompileNode());
				$N2f->LinkExecuteNode(new N2W\ModularRequestNode($Cfg));
			} else {
				$N2f->GetLogger()->Error("N2fWeb - Could not load nodes, missing configuration.");
			}

			return;
		}
	}
