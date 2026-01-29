# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report them via email to: security@zagros-cmms.local

You should receive a response within 48 hours. If for some reason you do not, please follow up via email to ensure we received your original message.

Please include the following information:

- Type of issue (e.g. buffer overflow, SQL injection, cross-site scripting, etc.)
- Full paths of source file(s) related to the manifestation of the issue
- The location of the affected source code (tag/branch/commit or direct URL)
- Any special configuration required to reproduce the issue
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit it

## Security Measures

### Application Security

- **Authentication**: JWT token-based authentication
- **Password Hashing**: bcrypt with automatic salt
- **SQL Injection**: Protected via Doctrine ORM parameterized queries
- **XSS Protection**: React auto-escaping + CSP headers
- **CSRF**: Tokens implemented for state-changing operations
- **Input Validation**: Symfony Validator + Zod (frontend)
- **Rate Limiting**: API and login endpoints rate-limited
- **HTTPS**: Required in production
- **Security Headers**: X-Frame-Options, X-Content-Type-Options, etc.

### Infrastructure Security

- **Docker**: No root users in containers
- **Database**: Not exposed to public internet (internal network only)
- **Redis**: Password protected, not exposed
- **Secrets**: Environment variables, never committed to git
- **Dependencies**: Automated scanning via Dependabot
- **Firewall**: Only ports 80/443 exposed in production

### Data Security

- **Encryption**: All passwords hashed before storage
- **Token Expiry**: JWT tokens expire after 1 hour
- **Audit Logs**: All critical actions logged
- **Backup**: Regular database backups (production)

## Security Best Practices

### For Developers

1. **Never commit secrets** to the repository
2. **Use environment variables** for all sensitive data
3. **Validate all input** on both frontend and backend
4. **Use parameterized queries** always
5. **Keep dependencies updated** regularly
6. **Run security scans** before committing
7. **Follow OWASP Top 10** guidelines
8. **Use HTTPS** in production
9. **Implement proper error handling** (don't leak sensitive info)
10. **Review code** for security issues before merging

### For Deployment

1. **Change all default passwords** in `.env`
2. **Use strong passwords** (minimum 32 characters for secrets)
3. **Enable firewall** on production servers
4. **Use HTTPS** with valid SSL certificates
5. **Keep software updated** (OS, Docker, dependencies)
6. **Monitor logs** for suspicious activity
7. **Implement backup strategy**
8. **Restrict SSH access**
9. **Use non-root users** everywhere
10. **Regular security audits**

## Known Security Considerations

### JWT Storage
- Tokens stored in localStorage (XSS risk if compromised)
- Consider using httpOnly cookies in production for enhanced security

### CORS
- Currently allows all origins in development
- Must be restricted to specific domains in production

### File Uploads
- File upload functionality not yet implemented
- When implemented, will include: file type validation, size limits, virus scanning

## Security Updates

Security updates will be released as soon as possible after a vulnerability is confirmed.

Users are encouraged to:
- Watch this repository for security announcements
- Subscribe to release notifications
- Keep installations up to date
- Review CHANGELOG.md for security fixes

## Acknowledgments

We appreciate responsible disclosure of security vulnerabilities.

Contributors who report valid security issues will be acknowledged (unless they prefer to remain anonymous).

## Contact

For security-related questions: security@zagros-cmms.local

For general questions: info@zagros-cmms.local
