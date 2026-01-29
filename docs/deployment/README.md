# Deployment Guide

## Local Development

### Prerequisites
- Docker Desktop 4.x+
- Git
- Text editor

### Quick Start

1. **Clone and Configure**
```bash
git clone https://github.com/yourusername/zagros-cmms.git
cd zagros-cmms
cp .env.example .env
```

2. **Edit `.env` file**
```bash
# Generate secure passwords
DB_PASSWORD=your_secure_password
REDIS_PASSWORD=your_secure_password
JWT_SECRET=$(openssl rand -base64 32)
JWT_PASSPHRASE=$(openssl rand -base64 32)
APP_SECRET=$(openssl rand -base64 32)
```

3. **Start Services**
```bash
docker-compose up -d
```

4. **Initialize Database**
```bash
# Wait for services to be healthy
docker-compose ps

# Run migrations
docker-compose exec backend php bin/console doctrine:migrations:migrate --no-interaction

# Load demo data
docker-compose exec backend php bin/console app:seed-demo-data
```

5. **Access Application**
- Frontend: http://localhost
- API Docs: http://localhost/api/docs
- Demo Login: `admin@zagros.test` / `admin123`

### Development Workflow

**Backend Development:**
```bash
# Access PHP container
docker-compose exec backend bash

# Install new package
docker-compose exec backend composer require package/name

# Run tests
docker-compose exec backend php bin/phpunit

# Clear cache
docker-compose exec backend php bin/console cache:clear
```

**Frontend Development:**
```bash
# Access frontend container
docker-compose exec frontend sh

# Install new package
docker-compose exec frontend npm install package-name

# Run tests
docker-compose exec frontend npm test

# Build
docker-compose exec frontend npm run build
```

**Database Access:**
```bash
# Access PostgreSQL
docker-compose exec postgres psql -U cmms -d zagros_cmms

# Backup database
docker-compose exec postgres pg_dump -U cmms zagros_cmms > backup.sql

# Restore database
docker-compose exec -T postgres psql -U cmms zagros_cmms < backup.sql
```

**Redis CLI:**
```bash
# Access Redis
docker-compose exec redis redis-cli -a your_redis_password

# Monitor
docker-compose exec redis redis-cli -a your_redis_password MONITOR
```

### Stopping Services

```bash
# Stop services
docker-compose stop

# Stop and remove containers
docker-compose down

# Remove containers and volumes (WARNING: deletes data)
docker-compose down -v
```

---

## Production Deployment

### Prerequisites
- VPS/Server with Ubuntu 22.04 LTS
- Docker & Docker Compose
- Domain name
- SSL certificate

### Server Setup

1. **Update System**
```bash
sudo apt update && sudo apt upgrade -y
```

2. **Install Docker**
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
```

3. **Install Docker Compose**
```bash
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

4. **Configure Firewall**
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Application Deployment

1. **Clone Repository**
```bash
git clone https://github.com/yourusername/zagros-cmms.git
cd zagros-cmms
```

2. **Configure Environment**
```bash
cp .env.example .env
nano .env
```

Set production values:
```env
APP_ENV=prod
APP_DEBUG=0
DB_PASSWORD=very_secure_password_here
REDIS_PASSWORD=another_secure_password
JWT_SECRET=generate_with_openssl_rand
# ... etc
```

3. **SSL Certificates**

**Option A: Let's Encrypt (Recommended)**
```bash
# Install certbot
sudo apt install certbot

# Generate certificate
sudo certbot certonly --standalone -d your-domain.com
```

**Option B: Self-signed (Development only)**
```bash
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem
```

4. **Update Nginx Config**
Edit `docker/nginx/prod.conf`:
```nginx
ssl_certificate /etc/nginx/ssl/cert.pem;
ssl_certificate_key /etc/nginx/ssl/key.pem;
```

5. **Build and Start**
```bash
# Build production images
docker-compose -f docker-compose.prod.yml build

# Start services
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker-compose -f docker-compose.prod.yml exec backend php bin/console doctrine:migrations:migrate --no-interaction

# Load demo data (optional)
docker-compose -f docker-compose.prod.yml exec backend php bin/console app:seed-demo-data
```

6. **Verify Deployment**
```bash
# Check services
docker-compose -f docker-compose.prod.yml ps

# Check logs
docker-compose -f docker-compose.prod.yml logs -f

# Test endpoints
curl https://your-domain.com/health
curl https://your-domain.com/api/docs
```

### Post-Deployment

**Set up automatic backups:**
```bash
# Create backup script
cat > /usr/local/bin/backup-zagros.sh << 'EOF'
#!/bin/bash
BACKUP_DIR=/backups/zagros-cmms
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup database
docker-compose -f /path/to/zagros-cmms/docker-compose.prod.yml exec -T postgres \
  pg_dump -U cmms zagros_cmms | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup uploads (if any)
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz /path/to/zagros-cmms/backend/var/uploads

# Keep only last 30 days
find $BACKUP_DIR -type f -mtime +30 -delete
EOF

chmod +x /usr/local/bin/backup-zagros.sh
```

**Add to crontab:**
```bash
crontab -e

# Add line:
0 2 * * * /usr/local/bin/backup-zagros.sh
```

### Monitoring

**Check resource usage:**
```bash
docker stats
```

**Application logs:**
```bash
# Backend logs
docker-compose -f docker-compose.prod.yml logs -f backend

# Nginx logs
docker-compose -f docker-compose.prod.yml logs -f nginx

# Database logs
docker-compose -f docker-compose.prod.yml logs -f postgres
```

### Updates

```bash
# Pull latest code
git pull origin main

# Rebuild images
docker-compose -f docker-compose.prod.yml build

# Stop services
docker-compose -f docker-compose.prod.yml down

# Start with new images
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker-compose -f docker-compose.prod.yml exec backend php bin/console doctrine:migrations:migrate --no-interaction
```

### Rollback

```bash
# Revert to previous version
git checkout previous-tag

# Rebuild and restart
docker-compose -f docker-compose.prod.yml build
docker-compose -f docker-compose.prod.yml up -d

# Restore database backup
gunzip < /backups/zagros-cmms/db_TIMESTAMP.sql.gz | \
  docker-compose -f docker-compose.prod.yml exec -T postgres psql -U cmms zagros_cmms
```

---

## Docker Compose Commands Reference

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose stop

# Restart services
docker-compose restart

# View logs
docker-compose logs -f [service_name]

# Execute command in container
docker-compose exec [service] [command]

# Rebuild specific service
docker-compose build [service]

# Scale service
docker-compose up -d --scale backend=3

# Remove everything
docker-compose down -v --rmi all
```

---

## Troubleshooting

### Database Connection Issues
```bash
# Check PostgreSQL is running
docker-compose ps postgres

# Check logs
docker-compose logs postgres

# Test connection
docker-compose exec postgres psql -U cmms -d zagros_cmms -c "SELECT version();"
```

### Permission Issues
```bash
# Fix backend permissions
docker-compose exec backend chown -R www-data:www-data /var/www/backend/var
```

### Cache Issues
```bash
# Clear Symfony cache
docker-compose exec backend php bin/console cache:clear

# Clear Redis
docker-compose exec redis redis-cli -a password FLUSHALL
```

### Container Won't Start
```bash
# Check logs
docker-compose logs [service_name]

# Rebuild from scratch
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

---

## Performance Tuning

### PostgreSQL
Edit `docker/postgres/init.sql`:
```sql
-- For production (8GB RAM server)
ALTER SYSTEM SET shared_buffers = '2GB';
ALTER SYSTEM SET effective_cache_size = '6GB';
ALTER SYSTEM SET maintenance_work_mem = '512MB';
ALTER SYSTEM SET work_mem = '32MB';
```

### PHP-FPM
Edit `docker/php/php-fpm.conf`:
```ini
pm.max_children = 100
pm.start_servers = 20
pm.min_spare_servers = 10
pm.max_spare_servers = 30
```

### Nginx Caching
Add to `docker/nginx/prod.conf`:
```nginx
proxy_cache_path /var/cache/nginx levels=1:2 keys_zone=api_cache:10m max_size=1g;
proxy_cache_key "$scheme$request_method$host$request_uri";
```

---

## Security Checklist

- [ ] Changed all default passwords in `.env`
- [ ] SSL certificates installed and working
- [ ] Firewall configured (only 80, 443, 22 open)
- [ ] Database not exposed to public internet
- [ ] Redis not exposed to public internet
- [ ] Regular backups configured
- [ ] Monitoring and alerting set up
- [ ] Dependencies kept up to date
- [ ] Security headers configured in Nginx
- [ ] Rate limiting enabled
- [ ] Fail2ban installed (optional)

---

## Support

For deployment issues:
- Check [GitHub Issues](https://github.com/yourusername/zagros-cmms/issues)
- Review [Documentation](../README.md)
- Check logs: `docker-compose logs`
