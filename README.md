# Laravel Task Manager

Web-based task management app, using Laravel 11, PHP 8.3+ and MySQL.

In this application, users can manage their tasks by creating, editing, deleting and filtering them according to the projects, also reordering tasks using drag-and-drop mechanism with automatic priority update in browser.

---

## Technical Overview & Architecture

The application was designed based on Laravel best practices and clean code principles:

- **Resourceful Controllers & Routing**: Handled routing through standard resourceful routes. Controller actions (`TaskController`, `ProjectController`) are focused and delegate validation and query logic to specialized classes.
- **Form Request Validation**: Every input is validated through dedicated Form Request classes (`StoreTaskRequest`, `UpdateTaskRequest`, `ReorderTasksRequest`, `StoreProjectRequest`), keeping controllers clean.
- **Eloquent Models & Relationships**:
  - `Project` has many `Task` instances.
  - `Task` belongs to a `Project` (tasks can also be unassigned).
  - Used local query scopes (`ordered()`) for consistent priority sorting.
- **Priority Logic & Data Integrity**:
  - Tasks maintain sequential priority numbers (`1, 2, 3...`) scoped to their assigned project.
  - Reordering via drag-and-drop submits the new ordering of task IDs to a dedicated endpoint (`/tasks/reorder`), which updates priority indices sequentially within a database transaction (`DB::transaction`) to guarantee atomicity.
  - Deleting a task automatically re-compacts the remaining priorities for that project to prevent sequence gaps.
- **Automated Testing**: Full feature test coverage (`tests/Feature/TaskManagementTest.php`) verifying CRUD behavior, priority auto-assignment, priority compaction on delete, drag-and-drop reordering, and project filtering.

---

## UI & Frontend Stack

#### This part i have done by AI.

The frontend is designed to be responsive, clean, and interactive without the need for a heavy SPA framework:

- **Tailwind CSS**: Used for responsive layout styling, consistent spacing, and custom color accents.
- **SortableJS**: A lightweight library used to power the drag-and-drop priority reordering. Priority badges in the DOM update instantly during dragging, followed by an asynchronous background request to persist the new order.
- **Blade Templates & Modals**: Built with reusable modal overlays for creating and editing tasks and projects, allowing users to manage their workflow without unnecessary page reloads.
- **Feedback & Notifications**: Includes immediate toast alerts for background AJAX operations as well as flash message banners for full-page form submissions.

---

## Requirements

- PHP 8.3 or higher
- MySQL 8.0+ or MariaDB 10.4+
- Composer
- Node.js (18+) & NPM

---

## Local Setup

1. **Clone the repository:**
this part you can avoid now --- 
but if you want you can download from there too - 
https://github.com/bchowdhury24/Laravel-Task-Manager.git

   ```bash
   git clone https://github.com/bchowdhury24/Laravel-Task-Manager.git
   cd Laravel-Task-Manager
   ```

2. **Install PHP and frontend dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure the environment file:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Update the database settings in `.env` if necessary:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel_task_manager
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Run migrations and seed sample data:**
   ```bash
   php artisan migrate --seed
   ```

5. **Build frontend assets:**
   ```bash
   npm run build
   ```

6. **Start the local server:**
   ```bash
   php artisan serve
   ```
   Open `http://127.0.0.1:8000` in your browser.

---

## Running Automated Tests

Run the test suite using PHPUnit / Artisan:

```bash
php artisan test
```

All 9 feature test cases cover:
- Dashboard task listing and project view
- Task creation with automatic and explicit priority assignment
- Task editing and updating
- Task deletion with automatic priority compaction
- Drag-and-drop priority reordering
- Filtering tasks by selected project
- Project creation

---

## Production Deployment Notes

When deploying to a production server:

1. **Document Root**: Configure your web server (Nginx or Apache) to point to the `public/` directory.
2. **Directory Permissions**:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```
3. **Production Build & Optimization**:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan migrate --force
   ```