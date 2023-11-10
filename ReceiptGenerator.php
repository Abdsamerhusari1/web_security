<?php
class ReceiptGenerator {
    public static function generate($blockchain, $transaction) {
        // Assuming $blockchain->chain is the array of blocks
        $blockNumber = $blockchain->getBlockCount();
        return [
            'transaction' => $transaction,
            'blockNumber' => $blockNumber,
            'timestamp' => time()
        ];
        
    }
}
?>
