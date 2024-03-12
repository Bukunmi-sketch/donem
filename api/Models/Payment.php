<?php

class Payment{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createPayment($userId, $shipmentId, $amount) {
        $stmt = $this->db->prepare("INSERT INTO payments (user_id, shipment_id, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $userId, $shipmentId, $amount);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getPaymentByShipment($shipmentId) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE shipment_id = ?");
        $stmt->bind_param("i", $shipmentId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    // Add other payment-related methods as needed
}
