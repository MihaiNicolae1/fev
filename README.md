# FEV

A full-stack web application built with Laravel 9 and React 18, featuring authentication, role-based access control, and CRUD operations.

## Tech Stack

- **Backend**: Laravel 9, PHP 8.0, Laravel Passport (OAuth2)
- **Frontend**: React 18, TypeScript, AG Grid, Tailwind CSS
- **Database**: MariaDB 10.4
- **Infrastructure**: Docker, Nginx

## Quick Start

```bash
# Clone and start
git clone https://github.com/MihaiNicolae1/fev.git
cd fev
make setup
```

Access the application:
- **Frontend**: http://localhost:3000
- **API**: http://localhost:8000/api

## Default Users

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | password | Admin (full access) |
| user@example.com | password | User (read-only) |

## Available Commands

```bash
make help          # Show all commands
make up            # Start containers
make down          # Stop containers
make test          # Run tests
make fresh         # Reset database
make logs          # View logs
```

## Project Structure

```
├── backend/           # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Models/
│   │   ├── Policies/
│   │   └── Repositories/
│   ├── database/
│   └── routes/api.php
├── frontend/          # React SPA
│   └── src/
│       ├── components/
│       ├── contexts/
│       ├── hooks/
│       ├── pages/
│       └── services/
├── docker-compose.yml
├── Makefile
└── nginx/
```

## Features

- JWT-based authentication with Laravel Passport
- Role-based access control (RBAC) with granular permissions
- Repository pattern for data access
- Form request validation
- API resource transformers
- PHPUnit test suite

## License

MIT
