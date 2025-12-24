<?php
// backend/strategies/VerificationStrategy.php
interface VerificationStrategy {
    public function sendCode($contact);
}