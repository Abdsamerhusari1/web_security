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

# Main function to run the program
def main():
    sender_public_key = input("Enter your public key: ")
    private_key_pem = input("Enter your private key: ")
    recipient = input("Enter recipient's address (webshop's wallet address): ")
    amount = float(input("Enter the amount to send: "))

    transaction_data = json.dumps({"sender": sender_public_key, "recipient": recipient, "amount": amount})
    signature = sign_data(transaction_data, private_key_pem)

    result = send_transaction(sender_public_key, recipient, amount, signature)
    print(result)

if __name__ == "__main__":
    main()
