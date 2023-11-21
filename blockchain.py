import hashlib
import json
from time import time
from uuid import uuid4
from flask import Flask, jsonify, request, redirect, render_template_string, url_for
from Crypto.PublicKey import RSA
from Crypto.Signature import pkcs1_15
from Crypto.Hash import SHA256
from textwrap import dedent
from urllib.parse import urlparse
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

    def new_transaction(self, sender, recipient, amount, signature, transaction_details):
        """
        Adds a new transaction to the list of transactions
        :param sender: <str> Public Key of the Sender
        :param recipient: <str> Public Key of the Recipient
        :param amount: <float> Amount
        :param signature: <str> Signature of the transaction
        :param transaction_details: <str> JSON string containing details of the transaction
        :return: <int> The index of the Block that will hold this transaction
        """
        self.current_transactions.append({
            'sender': sender,
            'recipient': recipient,
            'amount': amount,
            'signature': signature,
            'details': transaction_details  
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

# Load or define your store's public key
def load_store_public_key(file_path):
    with open(file_path, 'r') as file:
        return RSA.import_key(file.read())
store_public_key_path = 'store_public_key.pem'
store_public_key = load_store_public_key(store_public_key_path)

def verify_signature(public_key_str, signature_hex, order_details_json):
    try:
        public_key = RSA.import_key(public_key_str)
        order_hash = SHA256.new(order_details_json.encode())
        signature = bytes.fromhex(signature_hex)
        pkcs1_15.new(public_key).verify(order_hash, signature)
        return True
    except Exception as e:
        print("Verification Failed: " + str(e))
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
    blockchain.new_transaction(
        sender="0",
        recipient=node_identifier,
        amount=1,
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
  
@app.route('/create_transaction', methods=['POST'])
def create_transaction():
    try:
        values = request.get_json()
        print("Received transaction data:", values)

        sender_public_key_str = values.get('sender_public_key')
        print("sender_public_key_str:", sender_public_key_str)

        signature_hex = values.get('signature')
        print("signature_hex:", signature_hex)

        order_details = values.get('order_details')
        print("order_details:", order_details)

        amount = values.get('amount')
        print("amount:", amount)

        transaction_details = values.get('transaction_details')  # Additional transaction details
        print("transaction_details:", transaction_details)

        if not all([sender_public_key_str, signature_hex, order_details, amount]):
            print("Missing required transaction fields")
            return jsonify({'message': 'Missing required transaction fields', 'status': 'failed'}), 400

        try:
            sender_public_key = RSA.import_key(sender_public_key_str)
            h = SHA256.new(order_details.encode())
            signature = bytes.fromhex(signature_hex)
            pkcs1_15.new(sender_public_key).verify(h, signature)
            print("Signature verification: SUCCESS")
        except (ValueError, TypeError):
            print("Signature verification: FAILED")
            return jsonify({'message': 'Invalid signature', 'status': 'failed'}), 400

        index = blockchain.new_transaction(
            sender=sender_public_key_str,
            recipient=store_public_key.export_key().decode(),  # Convert store's public key to string
            amount=float(amount),  
            signature=signature_hex,
            transaction_details=transaction_details
        )
        print(f"Transaction will be added to Block {index}")
        return jsonify({'message': f'Transaction will be added to Block {index}', 'status': 'success'}), 200

    except Exception as e:
        print("Error in create_transaction:", str(e))
        return jsonify({'message': f'Error processing transaction: {str(e)}', 'status': 'failed'}), 400



@app.route('/chain', methods=['GET'])
def full_chain():
    response = {
        'chain': blockchain.chain,
        'length': len(blockchain.chain),
    }
    return jsonify(response), 200


@app.route('/')
def home():
    return "Hello, Blockchain!"


@app.route('/sign', methods=['POST'])
def sign_transaction():
    if 'privateKeyFile' not in request.files:
        return jsonify({'success': False, 'message': 'Private key file is missing'}), 400

    private_key_file = request.files['privateKeyFile']
    order_details = request.form.get('orderDetails')

    # Assuming order_details is a string of JSON data
    h = SHA256.new(order_details.encode())
    private_key = RSA.import_key(private_key_file.read())
    signature = pkcs1_15.new(private_key).sign(h)
    print("siiiiiiiiiiiiiiiggggnnnnnnn")
    print(signature.hex())
    print("                                                      ")
    print(order_details)
    print("                                                      ")

    return jsonify({
        'success': True,
        'signature': signature.hex(),
        'orderDetails': order_details
    })




if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5002)