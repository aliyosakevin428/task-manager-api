<div align="center">

# 📋 Task Manager API

**A multi-tenant, workspace-scoped Task Management REST API (Trello-lite) built with Laravel 13.**

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Sanctum](https://img.shields.io/badge/Auth-Sanctum-red?style=for-the-badge&logo=laravel)](https://laravel.com/docs/sanctum)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

[Features](#-key-features) • [Tech Stack](#-tech-stack) • [Installation Steps](#-installation-steps) • [API Documentation](#-api-documentation) • [License](#-license)

</div>

---

## 📌 Overview

**Task Manager API** is a robust API-only backend designed to power team collaboration apps like Trello or Asana. Built with multi-workspace support, team-scoped Role-Based Access Control (RBAC), private file storage, activity logging, and dynamic dashboard analytics out of the box.

---

## ✨ Key Features

- 🔐 **Authentication & Authorization**
    - Token-based API Auth via **Laravel Sanctum** (`/me`, Register, Login, Logout).
    - Multi-tenant **Workspace-Scoped RBAC** using `spatie/laravel-permission` (Teams feature).
    - Secure One-time Invite Tokens for fast onboarding and workspace joining.

- 🏢 **Multi-Workspace & Projects**
    - Create, list, and isolate workspace teams (`owner`, `admin`, `member`).
    - CRUD projects scoped strictly within workspace boundaries and protected by granular permissions.

- 📌 **Task Management & Workflows**
    - Full CRUD tasks scoped by workspace & project.
    - Advanced filtering (`status`, `assigned_to`, `q` search query), sorting (`id`, `due_date`, `priority`, `created_at` with `-` support for DESC), and pagination.
    - Soft-deletes with optional restoration & force-delete capabilities.
    - Interactive Task Comments (with update/delete rules for author or workspace moderators).
    - Private File Attachments (stored securely in `storage/app/`, authenticated download streams).

- 📊 **Activity Logs & Dashboard Analytics**
    - Complete **Activity Log** (Audit Trail) tracking important workspace actions (`task.created`, `task.updated`, `task.deleted`, `comment.*`, `member.role_changed`, `member.removed`).
    - Workspace Summary Dashboard (project count, total tasks, tasks grouped by status, due soon tasks, and personal assigned tasks).

---

## 🛠️ Tech Stack

| Component          | Technology / Package                                                                   |
| :----------------- | :------------------------------------------------------------------------------------- |
| **Framework**      | [Laravel 13](https://laravel.com)                                                      |
| **Language**       | PHP 8.2+                                                                               |
| **Database**       | MySQL 8.0+                                                                             |
| **Authentication** | [Laravel Sanctum](https://laravel.com/docs/sanctum)                                    |
| **RBAC / Teams**   | [Spatie Laravel-Permission](https://spatie.be/docs/laravel-permission) (Teams Enabled) |
| **API Docs**       | [Scramble](https://scramble.dedoc.co/) (Interactive OpenAPI Docs)                      |
| **Cache & Queue**  | Redis _(Optional)_                                                                     |

---

## 🔐 Workspace Roles & RBAC Matrix

Role permissions are dynamically evaluated within the active **Workspace Context** using `permission:*` middleware.

| Role          | Workspace Config |  Member Mgmt   |   Projects / Tasks   | Comments / Attachments |
| :------------ | :--------------: | :------------: | :------------------: | :--------------------: |
| 👑 **Owner**  |   Full Access    |  Full Access   |     Full Access      |      Full Access       |
| 🛡️ **Admin**  |    Read-Only     | Manage Members |     Full Access      |      Moderate Any      |
| 👤 **Member** |        ❌        |   View Only    | Create / Edit / View |       Own / View       |

---

## 🚀 Installation Steps

Follow these step-by-step instructions to get a local development environment up and running.

### Step 1: Prerequisites

Ensure you have the following installed on your machine:

- **PHP** >= 8.2
- **Composer**
- **MySQL** 8.0+
- **Git**

### Step 2: Clone the Repository

```bash
git clone [https://github.com/aliyosakevin428/task-manager-api.git](https://github.com/aliyosakevin428/task-manager-api.git)
cd task-manager-api
```

### Step 3: Install PHP Dependencies

```bash
composer install
```

### Step 4: Environment Configuration

```bash
cp .env.example .env
```

Generate Application encryption key:

```bash
php artisan key:generate
```

### Step 5: Database Setup

Open the .env file and configure your MySQL database credentials accordingly:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 6: Run Migrations & Seeders

Run database migrations along with seeders to build tables and populate default roles/permissions:

```bash
php artisan migrate --seed
```

### Step 7: Link Storage Directory

Link the private/public storage directory to handle task attachments properly:

```bash
php artisan storage:link
```

### Step 8: Start Local Development Server

Launch the Laravel local development server:

```bash
php artisan serve
```

📖 API Documentation & Testing
Interactive API documentation and endpoint explorer are powered by Scramble.

```bash
1. Make sure your application server is running via php artisan serve.

2. Open your browser and navigate to:

👉 http://127.0.0.0.1:8000/docs/api
```
