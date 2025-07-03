# Task Manager API

A robust Laravel-based Task Management API with authentication, task prioritization, full-text search, notifications, and advanced filtering.

---

## 🚀 Features

- **Authentication**
  - Register, login, and logout using Laravel Sanctum
- **Task Management**
  - Create, update status, soft delete, list, and search tasks
  - Status transitions: `Pending → In Progress → Completed`
- **Task Prioritization**
  - Assign priorities: Low, Medium, or High
- **Advanced Filtering**
  - Filter by status, due date range; sort by priority, due date, or created_at
- **Full-Text Search**
  - Uses Laravel Scout and Meilisearch for searching titles and descriptions
- **Reminders**
  - Hourly Artisan command queues email notifications 24h before task due
- **Rate Limiting**
  - Rate limiting behavior on task creation (5 requests/min)
- **Clean Architecture**
  - Controller → Service → Repository pattern
  - API Resources for clean and consistent JSON responses
- **API Documentation**
  - Swagger (OpenAPI) UI auto-generated via L5-Swagger

---

## 🛠 Tech Stack

- **Laravel 12**
- **PHP 8.2+**
- **MySQL** – Relational database
- **Laravel Sanctum** – API token authentication
- **Laravel Scout + Meilisearch** – Full-text search
- **Laravel Queues** – For sending notifications
- **L5-Swagger** – Auto-generated API documentation
- **PHP Enums** – Strongly-typed status and priority logic
- **Laravel Pint** – Opinionated PHP code style fixer for minimalists.

---

## 📦 Installation

```bash
git clone https://github.com/eslamayman1214/task-manager-api.git
cd task-manager-api

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan db:seed
````

**Generate Swagger Docs:**

```bash
php artisan l5-swagger:generate
```

**Run the app:**

```bash
php artisan serve
# Available at http://127.0.0.1:8000
```

**Run queue worker (reminders):**

```bash
php artisan queue:work --queue=notifications
```

**Run scheduler (reminder check):**

```bash
php artisan schedule:run
```

---

## 🔐 Authentication

Uses Laravel Sanctum. After login/register, include the token:

```
Authorization: Bearer {your_token}
```

---


## ⏰ Scheduled Task Reminders

This API includes a custom Artisan command to send email reminders 24 hours before a task is due:

```bash
php artisan tasks:send-reminders
```

```bash
php artisan tasks:send-reminders --dry-run
```

* The --dry-run option allows previewing which tasks would trigger reminders, without sending actual emails**



## 🧪 Testing

```bash
php artisan test
```

Includes:

* Unit & Feature tests
* Factories & seeders
* Auth & validation coverage

---

## 📚 API Documentation

Swagger UI:
[http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

Includes:

* Endpoint descriptions
* Request/response schemas
* Auth & error handling details

---

## 📌 Design Principles

* **SOLID** principles applied throughout services
* **Enum transitions** enforce business rules
* **Policy-based authorization** for per-user access control
* **Queue-based notifications** for scalable background tasks
* **Modular architecture** for clean, testable components

---

## 📂 Key Folders

* `app/Http/Controllers/Api/V1` – API endpoint controllers
* `app/Http/Requests` – Request validation logic
* `app/Http/Resources` – JSON response formatting
* `app/Services` – Business logic for tasks and auth
* `app/Repositories` – Data access and task queries
* `app/Filters` – Custom filters for task listing
* `app/Enums` – Task priority and status enums
* `app/Policies` – Authorization rules for tasks
* `app/Notifications` – Email notification classes
* `app/Console/Commands` – Artisan command for reminders
* `app/Util` – Reusable helpers (e.g., HTTP codes, pagination)

---

## 👨‍💻 Author

**Eslam Ayman**
GitHub: [https://github.com/eslamayman1214](https://github.com/eslamayman1214)
Email: [eslamayman1214@gmail.com](mailto:eslamayman1214@gmail.com)

```
