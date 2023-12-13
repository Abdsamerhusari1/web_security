import requests
import json
import base64
from cryptography.hazmat.primitives import serialization
from cryptography.hazmat.primitives.asymmetric import padding
from cryptography.hazmat.primitives import hashes

# Function to sign data with a private key
def sign_data(data, private_key_pem):
    private_key = serialization.load_pem_private_key(
        private_key_pem.encode(),
        password=None
    )
    signature = private_key.sign(
        data.encode(),
        padding.PKCS1v15(),
        hashes.SHA256()
    )
    return base64.b64encode(signature).decode('utf-8')

# Function to send transaction to the blockchain
def send_transaction(sender_public_key, recipient_hash, amount, signature):
    url = "http://localhost:5002/transactions/new"
    headers = {'Content-Type': 'application/json'}
    payload = {
        "sender": sender_public_key,
        "recipient": recipient_hash,
        "amount": amount,
        "signature": signature
    }
    response = requests.post(url, headers=headers, data=json.dumps(payload))
    return response.json()

def read_pem_file(file_path):
    with open(file_path, 'r') as file:
        return file.read()

def mine_block():
    response = requests.get('http://localhost:5002/mine')
    if response.status_code == 200:
        return response.json()
    else:
        return None

def main():
    sender_public_key_path = input("Enter your public key file path: ")
    private_key_pem_path = input("Enter your private key file path: ")
    recipient_hash = input("Enter store's hashed public key: ")
    amount = float(input("Enter the amount to send: "))

    #sender_public_key_path = 'public_key.pem'
    #private_key_pem_path = 'private_key.pem'
    #recipient_hash = 'a5da4d31a0a674f3ad9cdd3c83fc78176381e1769a3212718cc6a3ff800e03f9'
    #amount = float('11000000')

    sender_public_key = read_pem_file(sender_public_key_path)
    private_key_pem = read_pem_file(private_key_pem_path)

    transaction_data = json.dumps({
        "sender": sender_public_key,
        "recipient": recipient_hash,
        "amount": amount
    }, sort_keys=True)
    signature = sign_data(transaction_data, private_key_pem)


    #print("Signature: ", signature)
    #print("\n")
    #print("Transaction Data: ", transaction_data)
    #print("\n")
    #print("Sender Public Key PEM: ", sender_public_key)

    result = send_transaction(sender_public_key, recipient_hash, amount, signature)
    if 'Transaction will be added to Block' in result.get('message', ''):
        mine_result = mine_block()
        if mine_result:
            #print("Block mined successfully: ", mine_result)
            for transaction in mine_result['transactions']:
                print("Block mined successfully, The transaction ID ", transaction['id'])
                print("Amount", transaction['amount'])
                break

        else:
            print("Mining failed.")
    else:
        print("Transaction failed: ", result)


if __name__ == "__main__":
    main()
