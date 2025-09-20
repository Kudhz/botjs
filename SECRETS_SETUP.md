# GitHub Repository Secrets Configuration Guide

## Required Secrets for GitHub Actions Deployment

You need to configure these secrets in your GitHub repository:

### Go to: Repository Settings > Secrets and variables > Actions > Repository secrets

#### Required Secrets:

1. **SERVER_HOST**
   - Name: `SERVER_HOST`
   - Value: `192.168.1.177` (your aaPanel server IP)
   - Description: Server IP address or hostname

2. **SERVER_USER**
   - Name: `SERVER_USER`
   - Value: `root` (or your SSH username)
   - Description: SSH username for server access

3. **SERVER_SSH_KEY**
   - Name: `SERVER_SSH_KEY`
   - Value: Your private SSH key content
   - Description: SSH private key for passwordless authentication

### How to Generate SSH Key (if not already done):

```bash
# On your local machine or server
ssh-keygen -t rsa -b 4096 -C "github-actions@botjs"

# Copy public key to server
ssh-copy-id -i ~/.ssh/id_rsa.pub root@192.168.1.177

# Copy private key content for GitHub secret
cat ~/.ssh/id_rsa
```

### Alternative: Using Password Authentication

If you prefer password authentication instead of SSH keys, replace `SERVER_SSH_KEY` with:

- **SERVER_PASSWORD**
  - Name: `SERVER_PASSWORD`
  - Value: Your SSH password
  - Description: SSH password for server access

Then update the workflow to use `password` instead of `key` parameter.

### Verification:

After adding these secrets, the "Context access might be invalid" warnings will disappear, and your deployment will work properly.

### Current Secrets Status:
- ❌ SERVER_HOST (needs to be added)
- ❌ SERVER_USER (needs to be added)  
- ❌ SERVER_SSH_KEY (needs to be added)

Once these are configured, your GitHub Actions deployment will work without warnings.