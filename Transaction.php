<?php
class Transaction {
    public static function create($sender, $receiver, $amount) {
        return [
            'sender' => $sender,
            'receiver' => $receiver,
            'amount' => $amount
        ];
    }

    public static function sign($transaction, $privateKey) {
        $data = json_encode($transaction);
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return ['transaction' => $transaction, 'signature' => bin2hex($signature)];
    }
}
?>
