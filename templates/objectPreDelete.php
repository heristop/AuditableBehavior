if (!$this->isNew()) {
    $this->logActivity(<?php echo $peerName ?>::AUDIT_LABEL_DELETE, $con);
}