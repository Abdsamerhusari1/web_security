from cryptography.hazmat.primitives import serialization
from cryptography.hazmat.backends import default_backend
import hashlib

def hash_public_key(file_path):
    with open(file_path, "rb") as key_file:
        public_key = serialization.load_pem_public_key(
            key_file.read(),
            backend=default_backend()
        )

    public_key_bytes = public_key.public_bytes(
        encoding=serialization.Encoding.PEM,
        format=serialization.PublicFormat.SubjectPublicKeyInfo
    )

    hashed_key = hashlib.sha256(public_key_bytes).hexdigest()
    return hashed_key

public_key_path = 'keys/store_public_key.pem'
hashed_key = hash_public_key(public_key_path)
print("Hashed Public Key:", hashed_key)
