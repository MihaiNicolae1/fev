#!/bin/bash

# Assessment Application Setup Script
# This script sets up the dockerized application

set -e

echo "=========================================="
echo "  Assessment Application Setup"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

print_status "Docker and Docker Compose are installed"

# Create .env file for backend if it doesn't exist
if [ ! -f "backend/.env" ]; then
    if [ -f "backend/env.example" ]; then
        cp backend/env.example backend/.env
        print_status "Created backend/.env from env.example"
    else
        print_warning "backend/env.example not found"
    fi
else
    print_status "backend/.env already exists"
fi

# Build and start containers
echo ""
echo "Building and starting Docker containers..."
docker-compose up -d --build

# Wait for MariaDB to be ready
echo ""
echo "Waiting for MariaDB to be ready..."
sleep 10

# Check if MariaDB is ready
MAX_RETRIES=30
RETRY_COUNT=0
until docker-compose exec -T mariadb mysqladmin ping -h localhost -u admin -p0 --silent 2>/dev/null; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        print_error "MariaDB failed to start after $MAX_RETRIES attempts"
        exit 1
    fi
    echo "Waiting for MariaDB... (attempt $RETRY_COUNT/$MAX_RETRIES)"
    sleep 2
done

print_status "MariaDB is ready"

# Install backend dependencies
echo ""
echo "Installing backend dependencies..."
docker-compose exec -T backend composer install --no-interaction

print_status "Backend dependencies installed"

# Generate application key
echo ""
echo "Generating application key..."
docker-compose exec -T backend php artisan key:generate --force

print_status "Application key generated"

# Run migrations
echo ""
echo "Running database migrations..."
docker-compose exec -T backend php artisan migrate --force

print_status "Database migrations completed"

# Run seeders
echo ""
echo "Seeding database..."
docker-compose exec -T backend php artisan db:seed --force

print_status "Database seeded"

# Install Passport
echo ""
echo "Installing Laravel Passport..."
docker-compose exec -T backend php artisan passport:install --force

print_status "Laravel Passport installed"

# Create personal access client
echo ""
echo "Creating personal access client..."
docker-compose exec -T backend php artisan passport:client --personal --name="Personal Access Client" --no-interaction || true

print_status "Personal access client created"

# Clear caches
echo ""
echo "Clearing caches..."
docker-compose exec -T backend php artisan config:clear
docker-compose exec -T backend php artisan cache:clear

print_status "Caches cleared"

echo ""
echo "=========================================="
echo "  Setup Complete!"
echo "=========================================="
echo ""
echo "You can now access:"
echo "  - Frontend: http://localhost:3000"
echo "  - Backend API: http://localhost:8000/api"
echo "  - Via Nginx: http://localhost"
echo ""
echo "Test Users:"
echo "  - Admin: admin@example.com / password (full access)"
echo "  - User: user@example.com / password (read-only)"
echo ""
echo "Useful commands:"
echo "  docker-compose logs -f       # View logs"
echo "  docker-compose down          # Stop containers"
echo "  docker-compose restart       # Restart containers"
echo ""
