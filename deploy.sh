#!/bin/bash

# 🚀 FTP Deploy Script untuk aaPanel
# Usage: ./deploy-ftp.sh

echo "🏗️ Building React app..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Build failed!"
    exit 1
fi

echo "📦 Build successful!"

# Server info
FTP_HOST="192.168.1.177"
FTP_USER="kudh"
FTP_PASS="ikanasin"
FTP_DIR="/www/wwwroot/192.168.1.172"

echo "🚀 Uploading via FTP..."

# Install lftp jika belum ada
if ! command -v lftp &> /dev/null; then
    echo "📥 Installing lftp..."
    sudo apt-get update && sudo apt-get install -y lftp
fi

# Upload menggunakan lftp
lftp -e "
set ssl:verify-certificate no
open ftp://${FTP_USER}:${FTP_PASS}@${FTP_HOST}
mirror -R dist/ ./
quit
"

if [ $? -eq 0 ]; then
    echo "✅ Deployment successful!"
    echo "🌐 Your app should be available at: http://192.168.1.177:8008"
else
    echo "❌ FTP deployment failed!"
fi