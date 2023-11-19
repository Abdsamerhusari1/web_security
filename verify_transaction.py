from Crypto.PublicKey import RSA
from Crypto.Signature import pkcs1_15
from Crypto.Hash import SHA256
import sys
import json

def verify_signature(public_key_file, signature_hex, order_details_json):
    try:
        with open(public_key_file, 'r') as file:
            public_key = RSA.import_key(file.read())

        order_hash = SHA256.new(order_details_json.encode())
        signature = bytes.fromhex(signature_hex)

        pkcs1_15.new(public_key).verify(order_hash, signature)
        print("Verification Successful")
    except Exception as e:
        print("Verification Failed: " + str(e))
        sys.exit(1)

if __name__ == "__main__":
    public_key_file = sys.argv[1]
    signature_hex = sys.argv[2]
    order_details_json = sys.argv[3]

    verify_signature(public_key_file, signature_hex, order_details_json)
