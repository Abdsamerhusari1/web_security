from Crypto.PublicKey import RSA

# Generate private key
private_key = RSA.generate(2048)

# Serialize private key to PEM format
pem_private_key = private_key.export_key()

# Generate public key
public_key = private_key.publickey()

# Serialize public key to PEM format
pem_public_key = public_key.export_key()

# Write the keys to files
with open('store_private_key.pem', 'wb') as f:
    f.write(pem_private_key)

with open('store_public_key.pem', 'wb') as f:
    f.write(pem_public_key)

print("Keys generated and saved to files.")
