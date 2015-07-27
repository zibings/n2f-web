<?php

  class N2fWebLegacy extends N2f\ExtensionBase {
    public function Initialize(N2f\N2f &$N2f) {
    

      return;
    }
  }

  class N2fWebLegacyExecuteNode extends N2f\NodeBase {
    /**
     * Method to process a dispatch from a sender.
     *
     * @param mixed $Sender Object (or other) that initiated chain traversal.
     * @param N2f\DispatchBase $Dispatch Dispatch to process from sender.
     */
    public function Process($Sender, &$Dispatch) {
    }
  }

?>