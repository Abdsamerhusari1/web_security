import requests
from Crypto.PublicKey import RSA
from Crypto.Hash import SHA256
from requests_toolbelt.multipart.encoder import MultipartEncoder

# Generate matching public and private keys
key = RSA.generate(2048)
private_key = key.export_key()
public_key = key.publickey().export_key()

# Save the private key to a file
with open("private.pem", "wb") as f:
    f.write(private_key)

# Order details
order_details = '{"item_id": 123, "amount": 1000}'

# Step 1: Sign the transaction
multipart_data = MultipartEncoder(
    fields={
        'orderDetails': order_details,
        'privateKeyFile': ('private.pem', open('private.pem', 'rb'), 'text/plain')
    }
)

sign_response = requests.post('http://127.0.0.1:5002/sign', data=multipart_data, headers={'Content-Type': multipart_data.content_type})
sign_data = sign_response.json()




# Step 2: Create the transaction

def load_key_from_pem(file_path):
    with open(file_path, 'rb') as file:
        key_data = file.read()
        return RSA.import_key(key_data)
    
if sign_data['success']:
    recipient_public_key_path = 'store_public_key.pem'

    recipient_public_key = load_key_from_pem(recipient_public_key_path)

    transaction_data = {
        'sender_public_key': public_key.decode(),
        'recipient': recipient_public_key.export_key().decode(),
        'amount': 10,
        'signature': sign_data['signature'],
        'order_details': order_details  
    }
    print("                                            ")
    print(transaction_data)
    print("                                            ")

    create_transaction_response = requests.post('http://127.0.0.1:5002/create_transaction', json=transaction_data)
    print("Create Transaction Response:", create_transaction_response.json())
else:
    print("Failed to sign the transaction")
