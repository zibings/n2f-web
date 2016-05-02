<?php

	class N2fWebModular extends N2f\ExtensionBase {
		public function Initialize(N2f\N2f &$N2f) {
			$N2f->LinkConfigNode(new N2fWebModularConfigNode());
			$N2f->LinkExecuteNode(new N2fWebModularExecuteNode());
			$N2f->LinkGenerationNode(new N2fWebModularGenerateNode());

			return;
		}
	}

	class N2fWebModularConfigNode extends N2f\NodeBase {
		public function __construct() {
			$this->SetKey('N2fWebModularConfigNode')->SetVersion('1.0');

			return;
		}

		public function Process($Sender, N2f\DispatchBase &$Dispatch) {
			return;
		}
	}

	class N2fWebModularExecuteNode extends N2f\NodeBase {
		public function __construct() {
			$this->SetKey('N2fWebModularExecuteNode')->SetVersion('1.0');

			return;
		}

		public function Process($Sender, N2f\DispatchBase &$Dispatch) {
			return;
		}
	}

	class N2fWebModularGenerateNode extends N2f\NodeBase {
		public function __construct() {
			$this->SetKey('N2fWebModularGenerateNode')->SetVersion('1.0');

			return;
		}

		public function Process($Sender, N2f\DispatchBase &$Dispatch) {
			return;
		}
	}

?>