<?php
// backend/strategies/VerificationManager.php
class VerificationManager {
    private $strategy;

    public function __construct(VerificationStrategy $strategy) {
        $this->strategy = $strategy;
    }

    public function execute($contact) {
        return $this->strategy->sendCode($contact);
    }
}