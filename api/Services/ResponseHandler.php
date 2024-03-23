<?php 


class Response{

    public function sendResponse($message, $data = null, $statusCode = 200, $token = null) {
        $response = [
            'status' => '200',
            'message' => $message,
        ];
        
        if (!is_null($data)) {
            $response['data'] = $data;
        }
    
        if (!is_null($token)) {
            $response['token'] = $token;
        }
    
        http_response_code($statusCode);
        return json_encode($response);
    }
    
    // public function sendResponse($message, $data = null, $statusCode = 200) {
    //     $response = [
    //         'status' => '200',
    //         'message' => $message,
    //     ];
        
    //     if (!is_null($data)) {
    //         $response['data'] = $data;
    //     }

    //     http_response_code($statusCode);
    //     return json_encode($response);
    // }

    public function sendError($error, $message, $statusCode = 404) {
        $response = [
            'status' => '404',
            'error' => $error,
            'message' => $message,
        ];

        http_response_code($statusCode);
        return json_encode($response);
    }
}














?>