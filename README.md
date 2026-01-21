# Assessment Application

A fully dockerized full-stack application featuring:
- **Backend**: Laravel 9 (PHP 8.0.3) with Laravel Passport for authentication
- **Frontend**: React 18 with TypeScript and ag-Grid for data tables
- **Database**: MariaDB 10.4.14

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     Docker Environment                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│   ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│   │   Nginx     │    │  Frontend   │    │  Backend    │         │
│   │   Port 80   │───▶│  Port 3000  │    │  Port 8000  │         │
│   └─────────────┘    └─────────────┘    └─────────────┘         │
│          │                                    │                   │
│          └────────────────────────────────────┘                   │
│                              │                                    │
│                    ┌─────────────────┐                           │
│                    │    MariaDB      │                           │
│                    │    Port 3306    │                           │
│                    └─────────────────┘                           │
└─────────────────────────────────────────────────────────────────┘
```

## Quick Start

### Prerequisites
- Docker and Docker Compose installed
- Git

### Setup Instructions

1. **Clone and navigate to the project**
   ```bash
   cd /path/to/fev
   ```

2. **Start all containers**
   ```bash
   docker-compose up -d --build
   ```

3. **Install backend dependencies** (first time only)
   ```bash
   docker-compose exec backend composer install
   ```

4. **Generate application key**
   ```bash
   docker-compose exec backend php artisan key:generate
   ```

5. **Run database migrations and seeders**
   ```bash
   docker-compose exec backend php artisan migrate --seed
   ```

6. **Install Passport encryption keys**
   ```bash
   docker-compose exec backend php artisan passport:install
   ```

7. **Create personal access client for Passport**
   ```bash
   docker-compose exec backend php artisan passport:client --personal
   ```
   When prompted, you can press Enter to accept the default name.

8. **Access the application**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8000/api
   - Via Nginx: http://localhost

## Test Users

| Email | Password | Role | Access Level |
|-------|----------|------|--------------|
| admin@example.com | password | Web Administrator | Full CRUD access |
| user@example.com | password | User | Read-only access |

## Features

### Authentication & Authorization
- Laravel Passport OAuth2 token-based authentication
- Role-based access control (Webadmin / User)
- Protected API routes with middleware

### Data Table (ag-Grid)
- **ID**: Auto-generated unique identifier
- **Text Field**: Editable text input
- **Single Select**: Dropdown with predefined options
- **Multi Select**: Multi-value dropdown selection

### CRUD Operations
- **Create**: Add new records (Webadmin only)
- **Read**: View all records (All authenticated users)
- **Update**: Edit existing records inline (Webadmin only)
- **Delete**: Remove records (Webadmin only)

## Project Structure

```
fev/
├── docker-compose.yml          # Docker orchestration
├── nginx/
│   └── default.conf            # Nginx reverse proxy config
├── backend/                    # Laravel 9 API
│   ├── Dockerfile
│   ├── php.ini
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   ├── Middleware/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Models/
│   │   ├── Providers/
│   │   └── Repositories/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
│       └── api.php
└── frontend/                   # React 18 + TypeScript
    ├── Dockerfile
    ├── src/
    │   ├── components/
    │   ├── contexts/
    │   ├── hooks/
    │   ├── pages/
    │   ├── services/
    │   └── types/
    └── package.json
```

## API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/login` | Login with email/password |
| POST | `/api/auth/logout` | Logout (revoke token) |
| GET | `/api/auth/user` | Get current user info |

### Records
| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/api/records` | List all records | All users |
| GET | `/api/records/{id}` | Get single record | All users |
| POST | `/api/records` | Create record | Webadmin |
| PUT | `/api/records/{id}` | Update record | Webadmin |
| DELETE | `/api/records/{id}` | Delete record | Webadmin |

### Dropdown Options
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/dropdown-options` | Get all options grouped by type |
| GET | `/api/dropdown-options/{type}` | Get options by type |

## Development

### Backend Commands
```bash
# Run migrations
docker-compose exec backend php artisan migrate

# Run seeders
docker-compose exec backend php artisan db:seed

# Clear caches
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:clear

# View logs
docker-compose exec backend tail -f storage/logs/laravel.log
```

### Frontend Commands
```bash
# Install dependencies
docker-compose exec frontend npm install

# Build for production
docker-compose exec frontend npm run build

# Lint code
docker-compose exec frontend npm run lint
```

### Useful Docker Commands
```bash
# View logs
docker-compose logs -f

# Restart containers
docker-compose restart

# Stop all containers
docker-compose down

# Remove all data (including database)
docker-compose down -v
```

## Database Schema

### Tables
- `roles` - User roles (webadmin, user)
- `users` - Application users with role assignment
- `dropdown_options` - Configurable dropdown options
- `records` - Main data records
- `record_multi_options` - Many-to-many pivot for multi-select

## Extensibility

The application is built with extensibility in mind:

### Backend Patterns
- **Repository Pattern**: Abstract data access layer
- **Base Controller**: Common response methods
- **Form Request**: Validation abstraction
- **API Resources**: Response transformation

### Frontend Patterns
- **Generic API Service**: Reusable HTTP methods
- **Custom Hooks**: Encapsulated data fetching logic
- **TypeScript Interfaces**: Type-safe data handling
- **Context Providers**: Centralized state management

## Troubleshooting

### Database Connection Issues
```bash
# Ensure MariaDB is healthy
docker-compose exec mariadb mysqladmin ping -h localhost -u admin -p0

# Check database exists
docker-compose exec mariadb mysql -u admin -p0 -e "SHOW DATABASES;"
```

### Passport Issues
```bash
# Regenerate Passport keys
docker-compose exec backend php artisan passport:keys --force

# Clear config cache
docker-compose exec backend php artisan config:clear
```

### Frontend Not Loading
```bash
# Check Vite is running
docker-compose logs frontend

# Restart frontend container
docker-compose restart frontend
```

## Environment Variables

### Backend (.env)
```env
APP_NAME="Assessment App"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=assessment_db
DB_USERNAME=admin
DB_PASSWORD=0

FRONTEND_URL=http://localhost:3000
```

### Frontend
Configured via Vite environment:
- `VITE_API_URL`: Backend API URL (default: `/api`)

## License

This project is for assessment purposes.
