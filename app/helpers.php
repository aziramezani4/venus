<?php

function response_json($data,$status,$message,$isSuccess,$errors){

    $response = [
        'data'=> $data,
        'status' => $status,
        'message' => $message,
        'isSuccess' => $isSuccess,
        'errors' => [
            'message' => $errors,
        ],
    ];
    return response()->json($response, 200);
}
