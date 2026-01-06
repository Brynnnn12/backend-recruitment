# Recruitment Management System API

A professional Laravel-based backend API for managing recruitment processes in a company. Designed for HR to post jobs, review applications, and track applicant statuses, while allowing applicants to apply with secure file uploads.

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
