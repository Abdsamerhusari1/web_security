<?php
if (isset($error_message) && !empty($error_message)) {
	echo '<p style="color: red;">' . $error_message . '</p>';
}
?>
<?php

class Blockchain {
	private $chain;
	private $difficulty;

	public function __construct($difficulty = 4) {
		$this->chain = [];
		$this->difficulty = $difficulty;
	}

	private function calculateHash($block) {
		return hash('sha256', json_encode($block));
	}

	private function proofOfWork($block) {
		$block['nonce'] = 0;
		while (substr($this->calculateHash($block), 0, $this->difficulty) !== str_repeat("0", $this->difficulty)) {
			$block['nonce']++;
		}
		return $block;
	}

	public function addBlock($transactions, $previousHash) {
		$block = [
			'transactions' => $transactions,
			'previousHash' => $previousHash,
			'timestamp' => time()
		];
		$block = $this->proofOfWork($block);
		$this->chain[] = $block;
		return $block;
	}

	public function getLastBlock() {
		return end($this->chain);
	}
	public function getBlockCount() {
		return count($this->chain);
	}
}
?>
