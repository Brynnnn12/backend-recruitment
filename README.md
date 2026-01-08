# 🎯 Sistem Manajemen Rekrutmen - Backend API

API Backend profesional berbasis Laravel untuk mengelola proses rekrutmen di perusahaan. Dirancang untuk HR dalam memposting lowongan, meninjau aplikasi, dan melacak status pelamar, serta memungkinkan pelamar untuk melamar dengan upload file yang aman.

## ✨ Fitur Utama

### 🔐 Autentikasi & Otorisasi

-   Token-based authentication menggunakan Laravel Sanctum
-   Role-based permissions via Spatie Permission (Admin, HR, User)
-   Email verification untuk keamanan

### 💼 Manajemen Lowongan

-   HR dapat membuat, update, dan delete lowongan pekerjaan
-   Admin memiliki akses penuh ke semua lowongan
-   Status lowongan (open/closed)

### 📝 Sistem Aplikasi

-   Pelamar dapat apply lowongan dengan upload CV (PDF)
-   Update CV hanya bisa dilakukan saat status masih "applied"
-   Validasi duplicate application per user per vacancy

### 📊 Pelacakan Status

-   Status aplikasi: Applied, Reviewed, Interview, Hired, Rejected
-   HR/Admin dapat update status aplikasi
-   User dapat melihat status aplikasi mereka

### 📧 Notifikasi Email Otomatis

-   Email otomatis saat status berubah ke HIRED atau REJECTED
-   Template email profesional dan responsive
-   Queue system untuk pengiriman async

### ⏰ Auto-Reject Scheduler

-   Otomatis reject aplikasi yang tidak diproses selama 7 hari
-   Berjalan setiap hari pada pukul 00:00
-   Notifikasi email otomatis ke pelamar

### 🔒 Keamanan

-   File upload validation (PDF only, max 2MB)
-   Secure file storage dengan Laravel Storage
-   Policy-based authorization
-   XSS & CSRF protection

---

## 🛠️ Tech Stack

| Teknologi          | Deskripsi                     |
| ------------------ | ----------------------------- |
| **Framework**      | Laravel 12                    |
| **Authentication** | Laravel Sanctum               |
| **Authorization**  | Spatie Permission             |
| **Database**       | MySQL                         |
| **Queue**          | Database Queue Driver         |
| **Mail**           | SMTP / Log Driver             |
| **Validation**     | Form Request Validation       |
| **File Storage**   | Laravel Storage (Public Disk) |
| **Testing**        | Pest PHP                      |
| **API Docs**       | Scribe                        |

---

## 📦 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/Brynnnn12/backend-recruitment.git
cd backend-recruitment
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Konfigurasi Environment

Copy file `.env.example` ke `.env`:

```bash
cp .env.example .env
```

Edit konfigurasi di `.env`:

```env
APP_NAME="Recruitment System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=backend_recruitment
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

# Untuk testing, gunakan log
MAIL_MAILER=log

# Untuk production, gunakan SMTP
# MAIL_MAILER=smtp
# MAIL_HOST=smtp.gmail.com
# MAIL_PORT=587
# MAIL_USERNAME=your-email@gmail.com
# MAIL_PASSWORD=your-app-password
# MAIL_ENCRYPTION=tls
# MAIL_FROM_ADDRESS=noreply@recruitment.com
# MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Migrations & Seeders

```bash
php artisan migrate --seed
```

Ini akan membuat:

-   Table users, vacancies, applications
-   Roles: admin, hr, user
-   Sample users dengan role

### 6. Create Storage Link

```bash
php artisan storage:link
```

### 7. Start Application

**Terminal 1 - Application Server:**

```bash
php artisan serve
```

**Terminal 2 - Queue Worker (untuk email):**

```bash
php artisan queue:work --verbose
```

**Terminal 3 - Scheduler (untuk auto-reject):**

```bash
php artisan schedule:work
```

---

## 📚 API Documentation

Dokumentasi API lengkap tersedia di `/docs` setelah aplikasi berjalan.

### Akses Dokumentasi

1. Start server: `php artisan serve`
2. Buka browser: `http://localhost:8000/docs`
3. Gunakan "Try It Out" untuk testing langsung

Atau regenerate dokumentasi:

```bash
php artisan scribe:generate
```

---

## 🔑 API Endpoints

### Authentication

| Method | Endpoint                               | Deskripsi                 | Auth |
| ------ | -------------------------------------- | ------------------------- | ---- |
| POST   | `/api/register`                        | Register user baru        | No   |
| POST   | `/api/login`                           | Login dan dapatkan token  | No   |
| POST   | `/api/logout`                          | Logout                    | Yes  |
| POST   | `/api/email/verification-notification` | Resend verification email | Yes  |
| GET    | `/api/verify-email/{id}/{hash}`        | Verify email              | Yes  |

### Vacancies (Lowongan)

| Method | Endpoint              | Deskripsi           | Role      |
| ------ | --------------------- | ------------------- | --------- |
| GET    | `/api/vacancies`      | List semua lowongan | Admin, HR |
| POST   | `/api/vacancies`      | Buat lowongan baru  | Admin, HR |
| GET    | `/api/vacancies/{id}` | Detail lowongan     | Admin, HR |
| PUT    | `/api/vacancies/{id}` | Update lowongan     | Admin, HR |
| DELETE | `/api/vacancies/{id}` | Delete lowongan     | Admin, HR |

### Applications (Aplikasi)

| Method | Endpoint                           | Deskripsi       | Role                   |
| ------ | ---------------------------------- | --------------- | ---------------------- |
| GET    | `/api/applications`                | List aplikasi   | All (filtered by role) |
| POST   | `/api/applications`                | Apply lowongan  | User                   |
| GET    | `/api/applications/{id}`           | Detail aplikasi | Owner, Admin, HR       |
| POST   | `/api/applications/{id}/update-cv` | Update CV       | Owner (status=applied) |
| PUT    | `/api/applications/{id}/status`    | Update status   | Admin, HR              |
| DELETE | `/api/applications/{id}`           | Delete aplikasi | Admin only             |

---

## 👥 Roles & Permissions

| Role      | Permissions                                                                                                                                                                       |
| --------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Admin** | - Full access ke semua fitur<br>- Manage vacancies<br>- View/update/delete semua applications<br>- ❌ Tidak bisa apply job                                                        |
| **HR**    | - Manage vacancies<br>- View dan update semua applications<br>- Update status aplikasi<br>- ❌ Tidak bisa delete applications<br>- ❌ Tidak bisa apply job                        |
| **User**  | - Apply untuk lowongan<br>- View aplikasi sendiri<br>- Update CV sendiri (hanya saat status=applied)<br>- ❌ Tidak bisa update status<br>- ❌ Tidak bisa lihat aplikasi user lain |

---

## 🗄️ Database Schema

### Users Table

```
- id: bigint (PK)
- name: string
- email: string (unique)
- email_verified_at: timestamp (nullable)
- password: string
- remember_token: string (nullable)
- created_at: timestamp
- updated_at: timestamp
```

### Vacancies Table

```
- id: bigint (PK)
- title: string
- description: text
- location: string
- type: enum (full-time, part-time, contract, internship)
- status: enum (open, closed)
- created_by: bigint (FK → users.id)
- created_at: timestamp
- updated_at: timestamp
```

### Applications Table

```
- id: bigint (PK)
- user_id: bigint (FK → users.id)
- vacancy_id: bigint (FK → vacancies.id)
- cv_file: string
- status: enum (applied, reviewed, interview, hired, rejected)
- applied_at: timestamp
- created_at: timestamp
- updated_at: timestamp

Unique Key: (user_id, vacancy_id)
```

### Relationships

-   User → hasMany Applications
-   User → hasMany Vacancies (as creator)
-   Vacancy → hasMany Applications
-   Application → belongsTo User
-   Application → belongsTo Vacancy

---

## 📧 Sistem Email & Jobs

### Event-Driven Architecture

```
Status Update → Event Fired → Listener → Job Dispatched → Email Sent
```

### Flow Diagram

**Manual Status Update (Admin/HR):**

```
1. Admin/HR update status via API
2. ApplicationService::updateStatus()
3. Fire ApplicationStatusChanged event
4. SendApplicationStatusNotification listener
5. Dispatch SendApplicationStatusEmail job ke queue
6. Queue worker process job
7. ApplicationStatusMail terkirim
8. User menerima email
```

**Auto-Reject (Scheduler):**

```
1. Cron run: php artisan schedule:run (setiap menit)
2. Scheduler cek: Apakah waktu 00:00?
3. Ya → Dispatch AutoRejectStaleApplications job
4. Job cari aplikasi > 7 hari (status=applied)
5. Update status ke REJECTED
6. Fire event untuk setiap aplikasi
7. Email rejection otomatis terkirim
```

### Komponen

| Komponen              | File                                                  | Fungsi                                   |
| --------------------- | ----------------------------------------------------- | ---------------------------------------- |
| **Event**             | `app/Events/ApplicationStatusChanged.php`             | Announce perubahan status                |
| **Listener**          | `app/Listeners/SendApplicationStatusNotification.php` | Decide kirim email (HIRED/REJECTED only) |
| **Job - Email**       | `app/Jobs/SendApplicationStatusEmail.php`             | Send email async dengan retry            |
| **Job - Auto Reject** | `app/Jobs/AutoRejectStaleApplications.php`            | Auto reject aplikasi lama                |
| **Mailable**          | `app/Mail/ApplicationStatusMail.php`                  | Format email template                    |
| **Template**          | `resources/views/emails/application-status.blade.php` | HTML email                               |

### Konfigurasi Email

**Development (Testing):**

```env
MAIL_MAILER=log  # Email masuk ke storage/logs/laravel.log
```

**Production (Gmail):**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password  # App Password dari Google
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@recruitment.com
MAIL_FROM_NAME="Recruitment System"
```

**Setup Gmail:**

1. Enable 2-factor authentication
2. Generate App Password: https://myaccount.google.com/apppasswords
3. Gunakan App Password sebagai MAIL_PASSWORD

**Alternatif Mail Service:**

-   **Mailtrap** (testing): https://mailtrap.io - Free email testing
-   **SendGrid** (production): https://sendgrid.com - 100 emails/day gratis
-   **AWS SES** (production): https://aws.amazon.com/ses - Scalable & murah

### Testing Email System

**Test 1: Update Status Manual**

```bash
# Terminal 1: Queue Worker
php artisan queue:work --verbose

# Terminal 2: Update status
curl -X PUT http://localhost:8000/api/applications/1/status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "hired"}'

# Terminal 3: Watch logs
tail -f storage/logs/laravel.log
```

**Test 2: Auto-Reject Job**

```bash
# Dispatch manual
php artisan tinker
> App\Jobs\AutoRejectStaleApplications::dispatch();

# Atau jalankan scheduler
php artisan schedule:run
```

**Expected Logs:**

```
[INFO] Application status updated and event fired
[INFO] Dispatching email job for application status change
[INFO] Processing: App\Jobs\SendApplicationStatusEmail
[INFO] Application status email sent successfully
[INFO] Processed: App\Jobs\SendApplicationStatusEmail
```

### Monitoring

```bash
# Lihat scheduled tasks
php artisan schedule:list

# Lihat failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry {job-id}

# Retry semua failed jobs
php artisan queue:retry all

# Clear semua jobs
php artisan queue:clear

# Monitor logs real-time
tail -f storage/logs/laravel.log
```

---

## 🧪 Testing

Proyek ini menggunakan **Pest PHP** untuk testing.

### Run All Tests

```bash
./vendor/bin/pest
```

### Run Specific Test File

```bash
./vendor/bin/pest tests/Feature/Vacancies/VacanciesTest.php
./vendor/bin/pest tests/Feature/Applications/ApplicationsTest.php
```

### Test Coverage

**Vacancies (15 tests):**

-   ✅ Admin & HR dapat CRUD lowongan
-   ✅ User tidak bisa akses lowongan
-   ✅ Validation tests
-   ✅ Authorization tests

**Applications (20 tests):**

-   ✅ User dapat apply job dengan CV
-   ✅ User dapat update CV (status=applied only)
-   ✅ Admin/HR dapat update status
-   ✅ Admin dapat delete aplikasi
-   ✅ Prevent duplicate application
-   ✅ File upload validation
-   ✅ Authorization tests per role

**Total: 35 tests, 97 assertions**

---

## 🚀 Production Setup

### 1. Queue Worker (Supervisor)

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log
stopwaitsecs=3600
```

Start:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### 2. Scheduler (Cron)

Add ke crontab (`crontab -e`):

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Optimize Application

```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

---

## 🎓 Konsep & Best Practices

### 1. Repository Pattern

Memisahkan data access layer dari business logic.

```php
ApplicationRepository → ApplicationService → ApplicationController
```

**Benefits:**

-   Easy to test (mock repository)
-   Reusable queries
-   Clean code separation

### 2. Service Layer

Business logic terpusat di service layer.

```php
class ApplicationService {
    public function apply() { /* business logic */ }
    public function updateStatus() { /* business logic */ }
}
```

**Benefits:**

-   Fat models, thin controllers
-   Reusable business logic
-   Easy to maintain

### 3. Event-Driven Architecture

Loose coupling menggunakan events.

```php
// Service tidak perlu tahu tentang email
event(new ApplicationStatusChanged($app));

// Listener yang handle
class SendEmail {
    public function handle($event) { /* send email */ }
}
```

**Benefits:**

-   Easy to extend (tambah listener baru)
-   No tight coupling
-   Testable independently

### 4. Queue System

Background processing untuk task berat.

**Without Queue:**

-   User wait 3-5 detik untuk email terkirim ❌
-   If mail server down, request gagal ❌
-   Cannot scale ❌

**With Queue:**

-   Instant response ✅
-   Background processing ✅
-   Retry on failure ✅
-   Scalable (multiple workers) ✅

### 5. Policy-Based Authorization

Fine-grained access control.

```php
// Instead of if ($user->role === 'admin')
$this->authorize('update', $application);
```

**Benefits:**

-   Centralized authorization logic
-   Easy to test
-   Follows Laravel conventions

---

## 📁 Project Structure

```
app/
├── Enums/
│   └── ApplicationStatus.php           # Enum untuk status aplikasi
├── Events/
│   └── ApplicationStatusChanged.php    # Event saat status berubah
├── Exceptions/
│   └── ApiExceptionHandler.php         # Custom exception handling
├── Http/
│   ├── Controllers/
│   │   ├── ApplicationController.php   # Handle aplikasi CRUD
│   │   ├── UpdateStatusController.php  # Invokable untuk update status
│   │   └── VacancyController.php       # Handle lowongan CRUD
│   ├── Middleware/
│   ├── Requests/
│   │   ├── StoreApplicationRequest.php # Validation apply job
│   │   ├── UpdateCvRequest.php         # Validation update CV
│   │   ├── UpdateStatusRequest.php     # Validation update status
│   │   ├── StoreVacancyRequest.php     # Validation create vacancy
│   │   └── UpdateVacancyRequest.php    # Validation update vacancy
│   └── Resources/
│       ├── ApplicationResource.php     # Transform aplikasi response
│       └── VacancyResource.php         # Transform vacancy response
├── Jobs/
│   ├── SendApplicationStatusEmail.php  # Job kirim email
│   └── AutoRejectStaleApplications.php # Job auto-reject
├── Listeners/
│   └── SendApplicationStatusNotification.php # Listener email
├── Mail/
│   └── ApplicationStatusMail.php       # Mailable class
├── Models/
│   ├── Application.php                 # Model aplikasi
│   ├── User.php                        # Model user
│   └── Vacancy.php                     # Model lowongan
├── Policies/
│   ├── ApplicationPolicy.php           # Authorization aplikasi
│   └── VacancyPolicy.php               # Authorization lowongan
├── Providers/
│   ├── AppServiceProvider.php          # Main service provider
│   └── EventServiceProvider.php        # Event registration
├── Repositories/
│   ├── ApplicationRepository.php       # Data access aplikasi
│   └── VacancyRepository.php           # Data access lowongan
├── Services/
│   ├── ApplicationService.php          # Business logic aplikasi
│   ├── FileUploadService.php           # Handle file upload
│   └── VacancyService.php              # Business logic lowongan
└── Traits/
    └── ApiResponse.php                 # Standardize API response

resources/
└── views/
    └── emails/
        └── application-status.blade.php # Email template

routes/
├── api.php                             # API routes
├── auth.php                            # Auth routes
└── console.php                         # Scheduler configuration

tests/
└── Feature/
    ├── Applications/
    │   └── ApplicationsTest.php        # 20 test cases
    └── Vacancies/
        └── VacanciesTest.php           # 15 test cases
```

---

## 🔧 Troubleshooting

### Email Tidak Terkirim

**Cek:**

1. Queue worker running: `ps aux | grep queue:work`
2. Mail config di `.env` benar
3. Failed jobs: `php artisan queue:failed`
4. Logs: `tail -f storage/logs/laravel.log`

**Fix:**

```bash
# Restart queue worker
php artisan queue:restart

# Clear failed jobs
php artisan queue:flush

# Test email connection
php artisan tinker
> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));
```

### Queue Tidak Process

**Cek:**

1. `QUEUE_CONNECTION=database` di `.env`
2. Jobs table exists: `php artisan migrate`
3. Worker running: `php artisan queue:work --verbose`

**Fix:**

```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Restart worker
php artisan queue:restart
```

### Scheduler Tidak Jalan

**Cek:**

1. Scheduler terdaftar: `php artisan schedule:list`
2. Cron active: `crontab -l`
3. Timezone correct di `config/app.php`

**Fix:**

```bash
# Test manual
php artisan schedule:run

# Development mode
php artisan schedule:work

# Check cron logs
cat /var/log/syslog | grep schedule
```

### File Upload Gagal

**Cek:**

1. Storage link: `php artisan storage:link`
2. Permissions: `chmod -R 775 storage bootstrap/cache`
3. Disk config: `FILESYSTEM_DISK=public` di `.env`

**Fix:**

```bash
# Recreate storage link
rm public/storage
php artisan storage:link

# Fix permissions
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

---

## 🤝 Contributing

Contributions are welcome! Please follow:

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

### Code Standards

-   Follow PSR-12 coding standard
-   Write tests untuk fitur baru
-   Update documentation
-   Run `./vendor/bin/pest` sebelum commit

---

## 📄 License

This project is licensed under the MIT License.

---

## 👨‍💻 Developer

**Brynn** - [GitHub](https://github.com/Brynnnn12)

---

## 📞 Support

Jika ada pertanyaan atau butuh bantuan:

1. Check documentation di atas
2. Review error logs: `storage/logs/laravel.log`
3. Check API docs: `http://localhost:8000/docs`
4. Open issue di GitHub

---

## 🎉 Acknowledgments

-   Laravel Framework
-   Spatie Permission Package
-   Pest PHP Testing Framework
-   Scribe API Documentation

---

**Made with ❤️ using Laravel 11**

## Features

-   **Authentication & Authorization**: Token-based auth using Laravel Sanctum with role-based permissions via Spatie Permission.
-   **Job Management**: HR can create, update, and delete job postings.
-   **Application System**: Applicants can apply for jobs by uploading a single PDF file (CV + cover letter).
-   **Status Tracking**: HR can update application statuses (applied, reviewed, interview, hired, rejected).
-   **Secure File Upload**: Files stored securely in Laravel Storage with validation.

## Tech Stack

-   **Framework**: Laravel 11
-   **Authentication**: Laravel Sanctum
-   **Authorization**: Spatie Permission
-   **Database**: MySQL
-   **Validation**: Form Request Validation
-   **File Storage**: Laravel Storage
-   **API Documentation**: Swagger / Postman
-   **Testing**: PHPUnit

## Installation

1. **Clone the Repository**

    ```bash
    git clone <repository-url>
    cd backend-recruitment
    ```

2. **Install Dependencies**

    ```bash
    composer install
    ```

3. **Environment Configuration**

    - Copy `.env.example` to `.env`
    - Configure database settings:
        ```
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=recruitment_db
        DB_USERNAME=your_username
        DB_PASSWORD=your_password
        ```

4. **Generate Application Key**

    ```bash
    php artisan key:generate
    ```

5. **Run Migrations**

    ```bash
    php artisan migrate
    ```

6. **Seed Database (Roles & Admin)**

    ```bash
    php artisan db:seed
    ```

7. **Run the Application**

    ```bash
    php artisan serve
    ```

8. **Test API**
    - Import Postman collection from `postman_collection.json`
    - Test endpoints for auth, jobs, and applications

## API Endpoints

### Authentication

-   `POST /api/register` - Register a new user
-   `POST /api/login` - Login and get token
-   `POST /api/logout` - Logout

### Jobs

-   `GET /api/jobs` - List all jobs
-   `POST /api/jobs` - Create a job (HR only)
-   `PUT /api/jobs/{id}` - Update a job (HR only)
-   `DELETE /api/jobs/{id}` - Delete a job (Admin only)

### Applications

-   `POST /api/jobs/{id}/apply` - Apply for a job
-   `GET /api/applications` - List applications (HR only)
-   `PUT /api/applications/{id}/status` - Update application status (HR only)

## Roles & Permissions

| Role      | Permissions                       |
| --------- | --------------------------------- |
| Admin     | Manage users, jobs, view all data |
| HR        | Post jobs, review applications    |
| Applicant | Apply for jobs, upload CV         |

## Database ERD

### Tables

-   **users**: id, name, email, password, email_verified_at, created_at, updated_at
-   **jobs**: id, title, description, location, type, status, created_by, created_at, updated_at
-   **applications**: id, user_id, job_id, cv_file, status, applied_at, created_at, updated_at
-   **roles** (Spatie): id, name, guard_name
-   **model_has_roles** (Spatie): role_id, model_type, model_id

### Relationships

-   User (HR) hasMany Jobs
-   User (Applicant) hasMany Applications
-   Job hasMany Applications
-   Application belongsTo User and Job

## Agile Sprint Methodology

This project is developed using Agile Sprint methodology for efficient and iterative development.

### Sprint Structure

-   **Duration**: 1-2 weeks per sprint
-   **Activities**: Planning, Daily Standups, Review, Retrospective

### User Stories

1. **Sprint 1**: Authentication & Roles
2. **Sprint 2**: Job Management
3. **Sprint 3**: Application System
4. **Sprint 4**: Testing & Deployment

### Tools

-   **Project Management**: GitHub Issues / Trello
-   **Version Control**: Git
-   **Testing**: PHPUnit, Postman
-   **CI/CD**: GitHub Actions (optional)

## Contributing

Contributions are welcome! Please follow standard Laravel contribution guidelines.

## License

This project is licensed under the MIT License.
