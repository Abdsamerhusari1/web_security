import hashlib
import json
from time import time
from uuid import uuid4
import uuid
from flask import Flask, jsonify, request
from textwrap import dedent
from urllib.parse import urlparse
import requests
from cryptography.hazmat.primitives import serialization
from cryptography.hazmat.primitives.asymmetric import padding
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.backends import default_backend

import base64

counter = 0

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

        """
        Create a new Block in the Blockchain after verifying transactions
        """
        # Filter out transactions with invalid signatures
        #valid_transactions = [tx for tx in self.current_transactions if self.verify_transaction_signature(tx)]
        #self.current_transactions = valid_transactions

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

    def new_transaction(self, sender, recipient, amount, signature=None):
        # Adds a new transaction to the list of transactions
        # Creates a new transaction to go into the next mined Block
        """
        Creates a new transaction to go into the next mined Block
        :param id: <str> Unique identifier for the transaction
        :param sender: <str> Address of the Sender
        :param recipient: <str> Address of the Recipient
        :param amount: <int> Amount
        :return: <int> The index of the Block that will hold this transaction
        """

        transaction = {
            'id': str(uuid.uuid4()),  
            'sender': sender,
            'recipient': recipient,
            'amount': amount,
            'signature': signature,
        }

        # Verify the transaction signature
        if sender == "0" or self.verify_transaction_signature(transaction):
            # If the signature is valid, add the transaction to the list
            self.current_transactions.append(transaction)
            return self.last_block['index'] + 1
        else:
            # If the signature is invalid, do not add the transaction and return None
            return None


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



    def verify_transaction_signature(self, transaction):
        global counter

        if transaction['sender'] == "0":
            # Handling reward transaction. No need for signature verification.
            return True
        sender_public_key_pem = transaction['sender']
        #print("Debug: Public Key Before Verification:\n", sender_public_key_pem)  # Debugging line

        signature = base64.b64decode(transaction['signature'])
        transaction_data = json.dumps({
            'sender': transaction['sender'],
            'recipient': transaction['recipient'],
            'amount': transaction['amount']
        }, sort_keys=True).encode()

        counter += 1

        try:
            public_key = serialization.load_pem_public_key(
                sender_public_key_pem.encode(),
                backend=default_backend()
            )            
            print("\n")
            #print(f"This code has been reached {counter} times.")
        except ValueError as e:
            #print(f"This code has not been reached {counter} times.")

            print(f"Error loading public key: {e}")
            return False

        try:
            public_key.verify(
                signature,
                transaction_data,
                padding.PKCS1v15(),
                hashes.SHA256()
            )
            return True
        except Exception as e:
            print(f"Verification failed: {e}")
            return False

    

        
# Instantiate our Node
app = Flask(__name__)

# Generate a globally unique address for this node
node_identifier = str(uuid4()).replace('-', '')

# Instantiate the Blockchain
blockchain = Blockchain()

@app.route('/nodes/register', methods=['POST'])
def register_nodes():
    values = request.get_json()

    nodes = values.get('nodes')
    if nodes is None:
        return "Error: Please supply a valid list of nodes", 400

    for node in nodes:
        blockchain.register_node(node)

    response = {
        'message': 'New nodes have been added',
        'total_nodes': list(blockchain.nodes),
    }
    return jsonify(response), 201


@app.route('/nodes/resolve', methods=['GET'])
def consensus():
    replaced = blockchain.resolve_conflicts()

    if replaced:
        response = {
            'message': 'Our chain was replaced',
            'new_chain': blockchain.chain
        }
    else:
        response = {
            'message': 'Our chain is authoritative',
            'chain': blockchain.chain
        }

    return jsonify(response), 200

@app.route('/mine', methods=['GET'])
def mine():
    # We run the proof of work algorithm to get the next proof...
    last_block = blockchain.last_block
    last_proof = last_block['proof']
    proof = blockchain.proof_of_work(last_proof)

    # We must receive a reward for finding the proof.
    # The sender is "0" to signify that this node has mined a new coin.
    # Include a dummy signature for the reward transaction
    dummy_signature = str(uuid.uuid4())  # Generate a dummy signature
    blockchain.new_transaction(
        sender="0",
        recipient=node_identifier,
        amount=1,
        signature=dummy_signature,
    )

    # Forge the new Block by adding it to the chain
    previous_hash = blockchain.hash(last_block)
    block = blockchain.new_block(proof, previous_hash)

    response = {
        'message': "New Block Forged",
        'index': block['index'],
        'transactions': block['transactions'],
        'proof': block['proof'],
        'previous_hash': block['previous_hash'],
    }
    return jsonify(response), 200
  
@app.route('/transactions/new', methods=['POST'])
def new_transaction():
    values = request.get_json()
    required = ['sender', 'recipient', 'amount', 'signature']
    if not all(k in values for k in required):
        return 'Missing values', 400

    index = blockchain.new_transaction(values['sender'], values['recipient'], values['amount'], values['signature'])
    if index:
        response = {'message': f'Transaction will be added to Block {index}'}
        return jsonify(response), 201
    else:
        return jsonify({'message': 'Invalid transaction'}), 406


@app.route('/chain', methods=['GET'])
def full_chain():
    response = {
        'chain': blockchain.chain,
        'length': len(blockchain.chain),
    }
    return jsonify(response), 200

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5002)

#if __name__ == "__main__":
#    context = ('cert.pem', 'key.pem')  
#    app.run(host='0.0.0.0', port=5002, ssl_context=context)
    