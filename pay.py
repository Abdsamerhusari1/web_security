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
def send_transaction(sender_public_key, recipient, amount, signature):
    url = "http://localhost:5002/transactions/new"
    headers = {'Content-Type': 'application/json'}
    payload = {
        "sender": sender_public_key,
        "recipient": recipient,
        "amount": amount,
        "signature": signature
    }
    response = requests.post(url, headers=headers, data=json.dumps(payload))
    return response.json()

def read_pem_file(file_path):
    with open(file_path, 'r') as file:
        return file.read()
    
def main():
    sender_public_key_path = input("Enter your public key file path: ")
    private_key_pem_path = input("Enter your private key file path: ")
    recipient_path = input("Enter recipient's address file path: ")
    amount = float(input("Enter the amount to send: "))

    sender_public_key = read_pem_file(sender_public_key_path)
    private_key_pem = read_pem_file(private_key_pem_path)
    recipient = read_pem_file(recipient_path)
    
    transaction_data = json.dumps({"sender": sender_public_key, "recipient": recipient, "amount": amount})
    signature = sign_data(transaction_data, private_key_pem)

    result = send_transaction(sender_public_key, recipient, amount, signature)
    print(result)

if __name__ == "__main__":
    main()
