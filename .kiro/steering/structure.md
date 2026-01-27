# Project Structure

## Application Architecture

Laravel MVC with event-driven architecture for notifications and state submissions.

## Directory Organization

### `/app`

- **Console/Commands**: Artisan commands (certificate generation, course completion checks)
- **Events**: Domain events (UserEnrolled, PaymentApproved, CourseCompleted, CertificateGenerated)
- **Listeners**: Event handlers for email notifications
- **Observers**: Model observers (EnrollmentObserver, PaymentObserver)
- **Http/Controllers**: Request handlers organized by feature
- **Http/Middleware**: Request filtering and authentication
- **Models**: Eloquent models (100+ models for multi-state support)
- **Services**: Business logic services (CertificatePdfService, FlhsmvSoapService, PaymentService)
- **Mail**: Mailable classes for email templates
- **Notifications**: Push and email notifications
- **Rules**: Custom validation rules (CitationNumber, FloridaDriverLicense)

### `/database`

- **migrations**: Schema definitions (150+ migrations for multi-state tables)
- **seeders**: Data seeders organized by state (Florida, Missouri, Texas, Delaware)
- **factories**: Model factories for testing

### `/resources`

- **views**: Blade templates
  - `admin/`: Admin dashboard views
  - `course-player.blade.php`: Main course interface
  - `registration/`: Multi-step registration (step1-4)
  - `payment/`: Payment pages
  - `certificates/`: Certificate views
  - `emails/`: Email templates
  - `components/`: Reusable Blade components
- **js**: Vue components and JavaScript
- **css**: Tailwind CSS styles

### `/routes`

- `web.php`: Web routes (authentication, courses, payments, admin)
- `api.php`: API routes (if applicable)

### `/config`

State-specific and feature-specific configuration files.

## Key Patterns

### Event-Driven Architecture

Events trigger listeners for emails and notifications:
```
UserEnrolled → SendEnrollmentConfirmation
PaymentApproved → SendPaymentApprovedEmail
CourseCompleted → SendCourseCompletedEmail
CertificateGenerated → SendCertificateEmail
```

### Observer Pattern

Model observers handle side effects:
- `EnrollmentObserver`: Handles enrollment lifecycle
- `PaymentObserver`: Handles payment processing and invoice generation

### Service Layer

Business logic extracted to service classes:
- `CertificatePdfService`: PDF generation
- `FlhsmvSoapService`: Florida state submissions
- `PaymentService`: Payment processing
- `CourseTimerService`: Timer enforcement

### Multi-State Support

State-specific tables and models:
- `florida_*` tables for Florida-specific data
- `missouri_*` tables for Missouri-specific data
- Generic `courses` table with state differentiation

## Naming Conventions

- **Models**: Singular PascalCase (User, Course, Certificate)
- **Controllers**: PascalCase with Controller suffix (RegistrationController)
- **Views**: kebab-case (course-player.blade.php)
- **Routes**: kebab-case with dots (register.step, payment.show)
- **Database tables**: snake_case plural (user_course_enrollments)
- **Migrations**: timestamp_descriptive_name (2025_11_06_create_courses_table)

## State-Specific Organization

Each state has dedicated:
- Models (e.g., FloridaCertificate, MissouriStudent)
- Seeders (e.g., FloridaDefensiveDrivingSeeder, MissouriMasterSeeder)
- Controllers (state-specific logic in main controllers)
- Configuration (e.g., config/flhsmv.php for Florida)
