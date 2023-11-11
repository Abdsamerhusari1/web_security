import hashlib
import json
from time import time
from uuid import uuid4
from http.server import BaseHTTPRequestHandler, HTTPServer
from urllib.parse import urlparse, parse_qs
import threading

import requests

class Blockchain(object):
	"""docstring for Blockchain"""
	def __init__(self):
		self.chain = []
		self.current_transactions = []

		# Create the genesis block
		self.new_block(previous_hash=1, proof=100)

		self.nodes = set()
		
	def new_block(self, proof, previous_hash=None):
		"""
        Create a new Block in the Blockchain
        :param proof: <int> The proof given by the Proof of Work algorithm
        :param previous_hash: (Optional) <str> Hash of previous Block
        :return: <dict> New Block
        """

		# Creates a new Block and adds it to the chain

		block = {
            'index': len(self.chain) + 1,
            'timestamp': time(),
            'transactions': self.current_transactions,
            'proof': proof,
            'previous_hash': previous_hash or self.hash(self.chain[-1]),
		}

		# Reset the current list of transactions
		self.current_transactions = []
		self.chain.append(block)
		return block

	def new_transaction(self, sender, recipient, amount):
		# Adds a new transaction to the list of transactions
		# Creates a new transaction to go into the next mined Block
		"""
        Creates a new transaction to go into the next mined Block
        :param sender: <str> Address of the Sender
        :param recipient: <str> Address of the Recipient
        :param amount: <int> Amount
        :return: <int> The index of the Block that will hold this transaction
        """

		self.current_transactions.append({
		    'sender': sender,
		    'recipient': recipient,
		    'amount': amount,
		})
		return self.last_block['index'] + 1

	@staticmethod
	def hash(block):
		# Hashes a block
		"""
        Creates a SHA-256 hash of a Block
        :param block: <dict> Block
        :return: <str>
        """

        # We must make sure that the Dictionary is Ordered, or we'll have inconsistent hashes
		block_string = json.dumps(block, sort_keys=True).encode()
		return hashlib.sha256(block_string).hexdigest()
		
	@property
	def last_block(self):
		# Returns the last Block in the chain
		return self.chain[-1]

	def proof_of_work(self, last_proof):
		"""
		Simple Proof of Work Algorithm:
		 - Find a number p' such that hash(pp') contains leading 4 zeroes, where p is the previous p'
		 - p is the previous proof, and p' is the new proof
		:param last_proof: <int>
		:return: <int>
		"""

		proof = 0
		while self.valid_proof(last_proof, proof) is False:
			proof += 1

		return proof

	@staticmethod
	def valid_proof(last_proof, proof):
		"""
		Validates the Proof: Does hash(last_proof, proof) contain 4 leading zeroes?
		:param last_proof: <int> Previous Proof
		:param proof: <int> Current Proof
		:return: <bool> True if correct, False if not.
		"""

		guess = f'{last_proof}{proof}'.encode()
		guess_hash = hashlib.sha256(guess).hexdigest()
		return guess_hash[:4] == "0000"

	def register_node(self, address):
		"""
		Add a new node to the list of nodes
		:param address: <str> Address of node. Eg. 'http://192.168.0.5:5000'
		:return: None
		"""

		parsed_url = urlparse(address)
		self.nodes.add(parsed_url.netloc)

	def valid_chain(self, chain):
		"""
		Determine if a given blockchain is valid
		:param chain: <list> A blockchain
		:return: <bool> True if valid, False if not
		"""

		last_block = chain[0]
		current_index = 1

		while current_index < len(chain):
			block = chain[current_index]
			print(f'{last_block}')
			print(f'{block}')
			print("\n-----------\n")
			# Check that the hash of the block is correct
			if block['previous_hash'] != self.hash(last_block):
				return False

			# Check that the Proof of Work is correct
			if not self.valid_proof(last_block['proof'], block['proof']):
				return False

			last_block = block
			current_index += 1

		return True

	def resolve_conflicts(self):
		"""
		This is our Consensus Algorithm, it resolves conflicts
		by replacing our chain with the longest one in the network.
		:return: <bool> True if our chain was replaced, False if not
		"""

		neighbours = self.nodes
		new_chain = None

		# We're only looking for chains longer than ours
		max_length = len(self.chain)

		# Grab and verify the chains from all the nodes in our network
		for node in neighbours:
			response = requests.get(f'http://{node}/chain')

			if response.status_code == 200:
				length = response.json()['length']
				chain = response.json()['chain']

				# Check if the length is longer and the chain is valid
				if length > max_length and self.valid_chain(chain):
					max_length = length
					new_chain = chain

		# Replace our chain if we discovered a new, valid chain longer than ours
		if new_chain:
			self.chain = new_chain
			return True

		return False
	
# HTTP request handler class
class RequestHandler(BaseHTTPRequestHandler):
    blockchain = Blockchain()
    node_identifier = str(uuid4()).replace('-', '')

    def _set_response(self, status_code=200, body=''):
        self.send_response(status_code)
        self.send_header('Content-type', 'application/json')
        self.end_headers()
        if body:
            self.wfile.write(json.dumps(body).encode())

    def do_GET(self):
        if self.path == '/mine':
            # Mining logic here
            last_block = self.blockchain.last_block
            last_proof = last_block['proof']
            proof = self.blockchain.proof_of_work(last_proof)

            self.blockchain.new_transaction(
                sender="0",
                recipient=self.node_identifier,
                amount=1,
            )

            previous_hash = self.blockchain.hash(last_block)
            block = self.blockchain.new_block(proof, previous_hash)

            response = {
                'message': "New Block Forged",
                'index': block['index'],
                'transactions': block['transactions'],
                'proof': block['proof'],
                'previous_hash': block['previous_hash'],
            }
            self._set_response(200, response)

        elif self.path == '/chain':
            response = {
                'chain': self.blockchain.chain,
                'length': len(self.blockchain.chain),
            }
            self._set_response(200, response)

        else:
            self._set_response(404, {'message': 'Not found'})

    def do_POST(self):
        content_length = int(self.headers['Content-Length'])
        post_data = self.rfile.read(content_length)

        if self.path == '/transactions/new':
            data = json.loads(post_data)
            index = self.blockchain.new_transaction(data['sender'], data['recipient'], data['amount'])
            response = {'message': f'Transaction will be added to Block {index}'}
            self._set_response(201, response)

        else:
            self._set_response(404, {'message': 'Not found'})

def run(server_class=HTTPServer, handler_class=RequestHandler, port=5001):
    server_address = ('', port)
    server = server_class(server_address, handler_class)
    print(f'Starting HTTP server on port {port}...')
    server.serve_forever()

if __name__ == '__main__':
    run()