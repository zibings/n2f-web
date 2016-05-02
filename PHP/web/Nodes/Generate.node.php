<?php

	namespace N2f\N2fWeb;

	class GenerateNode extends \N2f\NodeBase {
		/**
		 * Method to process a dispatch from a sender.
		 *
		 * @param mixed $Sender Object (or other) that initiated chain traversal.
		 * @param \N2f\DispatchBase $Dispatch Dispatch to process from sender.
		 * @return void
		 */
		public function Process($Sender, \N2f\DispatchBase &$Dispatch) {
			if ($Sender === null || !($Sender instanceof \N2f\N2f) || !($Dispatch instanceof \N2f\GenerateDispatch)) {
				return;
			}

			return;
		}
	}