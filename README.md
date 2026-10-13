# Task Manager API (Laravel)

API-only Task Manager (Trello-lite) built with Laravel.  
Supports multi-workspace teams, role-based access control (RBAC), invite tokens, projects, tasks, comments, attachments, activity logs, and dashboard metrics.

## Tech Stack

- **Laravel** (13)
- **MySQL**
- **Laravel Sanctum** (API token auth)
- **Spatie Laravel Permission** (RBAC) + **Teams** (workspace-scoped roles)
- **Scramble** (API documentation / explorer)
- (Optional) Redis for cache/queue

---

## Features

### Authentication

- Register / Login / Logout (Sanctum token)
- Get current authenticated user (`/me`)

### Workspaces (Teams)

- Create & list workspaces
- Workspace membership (`owner`, `admin`, `member`)
- Invite token (one-time) + join workspace

### Projects

- CRUD projects scoped by workspace
- Protected by permissions

### Tasks

- CRUD tasks scoped by workspace & project
- Filtering + sorting + pagination
    - filter: `status`, `assigned_to`, `q`
    - sort: `id`, `due_date`, `priority`, `created_at` (supports `-column` for desc)
- Soft delete + (optional) restore / force delete

### Task Comments

- List / create / update / delete comments
- Update/Delete rules: author or workspace moderator (owner/admin)

### Task Attachments

- Upload file to task (stored in `storage/app/...`, private)
- List & delete attachments
- Download attachment (auth protected)

### Activity Logs (Audit Trail)

- Log important actions:
    - task.created / task.updated / task.deleted
    - comment.created / comment.updated / comment.deleted
    - member.role_changed / member.removed
- List activity logs per workspace

### Dashboard

- Workspace summary metrics:
    - projects count
    - total tasks
    - tasks grouped by status
    - due soon tasks
    - my assigned tasks

---

## RBAC & Roles (Workspace Scoped)

This project uses **Spatie Permission Teams** so roles are scoped per workspace.

Roles:

- `owner` – full access in workspace
- `admin` – manage members, projects, tasks, etc.
- `member` – can view projects/tasks, create tasks/comments, etc.

> Permissions are enforced using `permission:*` middleware inside workspace context.

---

## API Documentation (Scramble)

After running the server, open:

- `http://localhost:8000/docs/api`

---

## Requirements

- PHP 8.2+ (recommended)
- Composer
- MySQL 8+
- Node.js (optional, only if you need frontend tooling)

---

<!-- ## Installation

### 1) Clone

```bash
git clone https://github.com/aliyosakevin428/task-manager-api.git
cd task-manager-api
```

### 2) Install Dependencies

```bash
composer install

``` -->
