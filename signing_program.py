from flask import Flask, request, redirect, render_template_string, url_for
from Crypto.PublicKey import RSA
from Crypto.Signature import pkcs1_15
from Crypto.Hash import SHA256
import base64

app = Flask(__name__)

@app.route('/sign', methods=['GET', 'POST'])
def sign_transaction():
    if request.method == 'POST':
        if 'privateKeyFile' in request.files:
            # Read the order details from the form
            order_details = request.form['orderDetails']

            # Read the private key file
            file = request.files['privateKeyFile']
            private_key_pem = file.read()

            # Load the private key
            private_key = RSA.import_key(private_key_pem)

            # Create a hash of the transaction data
            h = SHA256.new(order_details.encode())

            # Sign the hash with the private key
            signature = pkcs1_15.new(private_key).sign(h)
            signature_hex = signature.hex()

            # Redirect back to the PHP webshop with the signature
            return redirect(f"https://localhost/webshop/verify_transaction.php?signature={signature_hex}&orderDetails={order_details}")
            pass
        else:
            # If not, render a form to upload the private key
            order_details = request.form['orderDetails']
            return render_template_string('''
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="orderDetails" value="{{ order_details }}">
                    <label>Upload your private key:</label>
                    <input type="file" name="privateKeyFile" required><br><br>
                    <input type="submit" value="Sign Transaction">
                </form>
            ''', order_details=order_details)
    else:
        # If it's a GET request, render a basic form or message
        return "Please go through the cart to proceed with the transaction."

if __name__ == '__main__':
    app.run(debug=True, port=5000)
