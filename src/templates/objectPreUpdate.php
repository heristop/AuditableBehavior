if ($this->isModified()) {
    $this->logActivity(<?php echo $peerName ?>::AUDIT_LABEL_UPDATE, $con);
}