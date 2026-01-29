#!/bin/sh
set -e

# Create JWT directory
mkdir -p config/jwt

# Generate private key
openssl genrsa -out config/jwt/private.pem 4096

# Generate public key
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

# Set permissions
chmod 644 config/jwt/private.pem
chmod 644 config/jwt/public.pem

echo "JWT keys generated successfully!"
