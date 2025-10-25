<div align="center">

# 📋 Task Management System

### A powerful RESTful Laravel API for task management with role-based access control

[![Laravel](https://img.shields.io/badge/Laravel-8.83.27-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Passport](https://img.shields.io/badge/OAuth2-Passport-00D8FF?style=for-the-badge&logo=passport&logoColor=white)](https://laravel.com/docs/8.x/passport)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

[Features](#-features) • [Installation](#-installation) • [API Documentation](#-api-documentation) • [Testing](#-testing) • [Contributing](#-contributing)

</div>

---

## 📖 About

**Task Management API** is a production-ready RESTful API built with Laravel 8 that provides comprehensive task management capabilities with user authentication, role-based authorization, and complete audit logging. Perfect for teams looking to integrate task management into their applications.

### ✨ Key Features

- 🔐 **OAuth2 Authentication** - Secure authentication using Laravel Passport
- 👥 **Role-Based Access Control** - Admin and User roles with granular permissions
- ✅ **Task Management** - Complete CRUD operations for tasks with assignment capabilities
- 📊 **Audit Logging** - Track all system activities and changes
- 📚 **Swagger Documentation** - Interactive API documentation out of the box
- 🧪 **Comprehensive Testing** - Full test coverage with PHPUnit
- 🚀 **RESTful Architecture** - Clean, predictable API endpoints

---

## 🛠 Tech Stack

| Category | Technology |
|----------|-----------|
| **Framework** | Laravel 8.83.27 |
| **Language** | PHP 8.0+ |
| **Database** | MySQL 8.0+ |
| **Authentication** | Laravel Passport (OAuth2) |
| **API Documentation** | L5-Swagger |
| **Testing** | PHPUnit |

---

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP** >= 8.0
- **Composer** >= 2.0
- **MySQL** >= 8.0
- **Git**

---

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/ebrahimashraf1996/task_management_system
cd task-management-api
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Open `.env` and update your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations & Seeders

```bash
# Create database tables
php artisan migrate

# Seed default data (Admin & User accounts)
php artisan db:seed
```

### 6. Create Passport Client 

```bash
# Create Passport Client
php artisan passport:client --personal
php artisan passport:keys
```


### 7. Start the Development Server

```bash
php artisan serve
```

Your API is now running at `http://localhost:8000` 🎉

---

## 🔐 Authentication

This API uses **OAuth2** authentication via Laravel Passport with Bearer tokens.

### Default Seeded Users

After running seeders, you'll have access to:

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@example.com | `password` |
| **User** | user@example.com | `password` |

### User Or Admin Registration

**Endpoint:** `POST /api/register`

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "role": "user" 
}
```
Replace user With admin For Admin Registration


---

### User Or Admin Login

**Endpoint:** `POST /api/login`

```json
{
  "email": "john@example.com",
  "password": "password",
}
```

---

## 📚 API Documentation

### Interactive Documentation

Full interactive API documentation is available via Swagger UI:

```
http://localhost:8000/api/doc
```

### Core Resources

#### 👤 Users (Admin only Can Access These Apis)
- `GET /api/users` - List all users
- `GET /api/users/{id}` - Get user details
- `POST /api/users` - Create new user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

#### ✅ Tasks
- `GET /api/tasks` - List all tasks
- `GET /api/tasks/{id}` - Get task details
- `POST /api/tasks` - Create new task
- `PUT /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task

#### 📊 Audit Logs
- `GET /api/audit-logs` - List all audit logs (Admin only)

---

## Testing

This project includes comprehensive test coverage using PHPUnit.

### Running Tests

```bash
# Run all tests
php artisan test
```

### Test Structure

```
tests/
    Feature/
    ├── AuthTest.php
    ├── TaskTest.php
    └── UserTest.php
```

### Test Database

Tests use **SQLite in-memory database** for fast, isolated testing. Configuration is automatic.

---

## 🚦 API Status Codes

| Code | Meaning |
|------|---------|
| `200` | Success |
| `401` | Unauthorized |
| `403` | Forbidden |
| `404` | Not Found |
| `422` | Validation Error |
| `500` | Server Error |

---


## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Author

**Ibrahim Ashraf**

- GitHub: [@ibrahimashraf](https://github.com/ebrahimashraf1996)
- Email: ebrahimashraf1996@gmail.com

---


Made with ❤ by Ibrahim Ashraf

