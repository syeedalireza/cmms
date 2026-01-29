# Security Setup Guide

This document provides security guidelines for the Zagros CMMS project.

## ⚠️ IMPORTANT: Before First Commit

### 1. Git Configuration (Privacy)

Never use your personal email in public repositories. Use GitHub's noreply email:

```bash
# Check your GitHub noreply email at: https://github.com/settings/emails
git config user.email "noreply@zagros-cmms.dev"
git config user.name "Your Public Name"

# Or use GitHub's noreply email:
git config user.email "username@users.noreply.github.com"
git config user.name "Your Name"
```

### 2. Enable GitHub Email Privacy

1. Go to https://github.com/settings/emails
2. Enable: **"Keep my email addresses private"**
3. Enable: **"Block command line pushes that expose my email"**
4. Use the provided noreply email for commits

### 3. Environment Files

**NEVER commit `.env` files!**

```bash
# Always verify .gitignore before first commit:
cat .gitignore | grep ".env"

# Should show:
# .env
# .env.local
# .env.*.local
# backend/.env
# backend/.env.local
```

### 4. Generate Secure Secrets

Before running the application, generate secure secrets:

```bash
# For Linux/Mac:
openssl rand -base64 32

# For Windows (PowerShell):
[Convert]::ToBase64String((1..32 | ForEach-Object { Get-Random -Maximum 256 }))
```

Update your `.env` file with these values:
- `DB_PASSWORD`
- `REDIS_PASSWORD`
- `JWT_SECRET`
- `JWT_PASSPHRASE`
- `APP_SECRET`

## 🔒 Security Checklist

- [ ] Git email set to noreply address
- [ ] GitHub email privacy enabled
- [ ] `.env` file created from `.env.example`
- [ ] All secrets in `.env` are unique and secure
- [ ] Verified `.env` is in `.gitignore`
- [ ] Never committed sensitive data
- [ ] Reviewed all files before `git add`

## 🚨 If You Accidentally Committed Secrets

### Option 1: Delete and Recreate Repository (Recommended)

1. Delete the GitHub repository
2. Remove git remote: `git remote remove origin`
3. Rotate ALL secrets (generate new passwords/keys)
4. Create new repository
5. Push clean code

### Option 2: Rewrite Git History (Advanced)

```bash
# Remove sensitive file from all history:
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch .env" \
  --prune-empty --tag-name-filter cat -- --all

# Force push (destructive!):
git push origin --force --all
git push origin --force --tags
```

**⚠️ After history rewrite, you MUST rotate ALL secrets!**

## 📋 Production Deployment Security

1. **Never use default passwords**
2. **Use environment variables for all secrets**
3. **Enable HTTPS with valid SSL certificates**
4. **Configure firewall rules**
5. **Regular security updates**
6. **Enable rate limiting**
7. **Configure CORS properly**
8. **Use strong JWT secrets (min 32 characters)**

## 🔐 Password Requirements

- Database passwords: min 16 characters, mixed case, numbers, symbols
- JWT secrets: min 32 characters
- Redis passwords: min 16 characters
- App secrets: min 32 characters

## 📞 Security Issues

If you discover a security vulnerability, please email:
`security@zagros-cmms.local`

**DO NOT** create public GitHub issues for security vulnerabilities.

---

**Last Updated:** January 29, 2026
**Version:** 1.0.0
