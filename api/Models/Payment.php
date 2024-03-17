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


    
    public function updatePayment($orderid, $payment_status, $payment_type, $order_status,$notifynewpay){

        try{
         
          $updatepay="UPDATE orders SET payment_status=:payment_status, order_status=:order_status, payment_type=:payment_type , notify_newpay=:notifynewpay  WHERE order_id=:orderid";
      
          $stmt=$this->db->prepare($updatepay);
          $stmt->bindParam(":payment_status", $payment_status);
          $stmt->bindParam(":order_status", $order_status);
          $stmt->bindParam(":payment_type", $payment_type);
          $stmt->bindParam(":orderid", $orderid);
          $stmt->bindParam(":notifynewpay", $notifynewpay);
          if( $stmt->execute()){
                       return true;
                     }else{
                    //echo "error while updating";
                 return false;
               }
          } catch(PDOException $e){
                 echo  $e->getMessage(); 
             }
      
      }

       public function confirmPayment($orderid, $confirmpayment, $transaction_ref)
       {
        try{
         
          $confirmpay="UPDATE orders SET  payment_confirmation=:payment_confirmation, transaction_ref =:transaction_ref WHERE order_id=:orderid";
      
          $stmt=$this->db->prepare($confirmpay);
          $stmt->bindParam(":payment_confirmation", $confirmpayment);
          $stmt->bindParam(":transaction_ref", $transaction_ref);
          $stmt->bindParam(":orderid", $orderid);
         
          if( $stmt->execute()){
                return true;
          }else{
                    //echo "error while updating";
              return false;
               }
          } catch(PDOException $e){
                 echo  $e->getMessage(); 
             }
      
      }        

      public function checkPaymentMethod($orderid, $payment_type){

          $confirmpay="UPDATE orders SET  payment_type=:payment_type WHERE order_id=:orderid";
      
          $stmt=$this->db->prepare($confirmpay);
          $stmt->bindParam(":payment_type",$payment_type );
          $stmt->bindParam(":orderid", $orderid);
         
          if( $stmt->execute()){
                return true;
          }else{
                    //echo "error while updating";
              return false;
               }
         

      }
   

    // Add other payment-related methods as needed
}
