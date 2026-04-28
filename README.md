# Laravel Boilerplate

A clean, production-ready Laravel boilerplate with admin panel, user management, roles & permissions, and API structure. Perfect starting point for your next Laravel project.

![Laravel](https://img.shields.io/badge/Laravel-11.47-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## ✨ Features

### 🔐 Authentication & Authorization
- ✅ User registration and login
- ✅ Admin panel with role-based access control (RBAC)
- ✅ Password reset functionality
- ✅ Email verification support
- ✅ Separate admin and user login flows

### 👥 Admin Panel
- ✅ User management (CRUD operations)
- ✅ Role management (CRUD operations)
- ✅ Permission management (CRUD operations)
- ✅ Manager/Editor management
- ✅ Dashboard with statistics
- ✅ Modern, responsive UI with Tailwind CSS

### 👤 User Panel
- ✅ User dashboard
- ✅ Profile management
- ✅ Settings page

### 🔌 API Structure
- ✅ Laravel Sanctum for API authentication
- ✅ RESTful API endpoints
- ✅ Standardized API responses
- ✅ API key middleware support

### 🎨 Frontend
- ✅ Blade templates with Tailwind CSS
- ✅ Modern, gradient-based color theme (Indigo/Purple)
- ✅ Responsive design
- ✅ Clean component structure
- ✅ DataTables integration

### 🔧 Services Integration (Configured but Empty)
- ✅ AWS S3 for file storage (structure ready)
- ✅ Firebase for real-time features (structure ready)
- ✅ Email service (SMTP/Mailgun/Postmark/SES)

## 📋 Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18.x & NPM
- MySQL/PostgreSQL/SQLite
- (Optional) AWS S3 account
- (Optional) Firebase account

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/ubaidkodekaizen/laravel-boilerplate.git
cd laravel-boilerplate
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install NPM Dependencies

```bash
npm install
```

### 4. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure Database

Edit `.env` file and update your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_boilerplate
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 6. Run Migrations

```bash
php artisan migrate
```

This will create all necessary tables including:
- `users` - User accounts
- `roles` - System roles (Admin, Manager, Editor, Member)
- `permissions` - System permissions
- `role_permissions` - Role-Permission relationships
- `user_permissions` - User-specific permissions

### 7. Create Admin User

```bash
php artisan admin:create
```

Or with custom credentials:

```bash
php artisan admin:create --email=admin@example.com --password=your_password --name=Admin
```

**Default Admin Credentials:**
- Email: `admin@example.com`
- Password: `password`

### 8. Build Assets

```bash
npm run build
```

### 9. Start Development Server

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` in your browser.

## 👥 Default Roles

The boilerplate comes with the following default roles:

1. **Admin** (ID: 1) - Full access to all features
2. **Manager** (ID: 2) - Can manage users and content
3. **Editor** (ID: 3) - Can edit content
4. **Member** (ID: 4) - Regular user with limited access

## 🔑 Access Points

### Web Routes

- **Home:** `http://127.0.0.1:8000/`
- **User Login:** `http://127.0.0.1:8000/login`
- **User Register:** `http://127.0.0.1:8000/register`
- **User Dashboard:** `http://127.0.0.1:8000/dashboard`
- **Admin Login:** `http://127.0.0.1:8000/admin/login`
- **Admin Dashboard:** `http://127.0.0.1:8000/admin/dashboard`

### Admin Panel Routes

- **Users Management:** `/admin/users`
- **Roles Management:** `/admin/roles`
- **Permissions Management:** `/admin/permissions`
- **Managers/Editors:** `/admin/managers` (Admin only)

## 🔧 Configuration

### AWS S3 (Optional)

If you want to use S3 for file storage:

1. Get your AWS credentials from AWS Console
2. Update `.env`:

```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name
```

### Firebase (Optional)

If you want to use Firebase:

1. Download your Firebase service account JSON file
2. Place it in `storage/app/firebase-credentials.json`
3. Update `.env`:

```env
FIREBASE_CREDENTIALS=firebase-credentials.json
FIREBASE_DATABASE_URL=https://your-project.firebaseio.com
FIREBASE_PROJECT_ID=your-project-id
```

### Email Service (Optional)

Configure your email service in `.env` based on your provider:

**SMTP (Recommended for Development - Use Mailtrap):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

**For Testing (Logs emails to storage/logs/laravel.log):**
```env
MAIL_MAILER=log
```

**Mailgun:**
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your_domain
MAILGUN_SECRET=your_secret
MAIL_FROM_ADDRESS=noreply@your_domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Postmark:**
```env
MAIL_MAILER=postmark
POSTMARK_TOKEN=your_token
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Test Email Functionality:**
```bash
php artisan email:test all your@email.com
```

This will send test emails for password reset, email verification, and welcome emails.

## 📡 API Usage

### Base URL
```
http://127.0.0.1:8000/api
```

### Register User
```bash
POST /api/register
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Login
```bash
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

Response:
```json
{
    "status": true,
    "message": "Login successful",
    "data": {
        "user": {...},
        "token": "1|xxxxxxxxxxxx"
    }
}
```

### Get Authenticated User
```bash
GET /api/user
Authorization: Bearer {token}
```

Response:
```json
{
    "status": true,
    "data": {
        "user": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            ...
        }
    }
}
```

### Update Profile
```bash
PUT /api/user
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### Forgot Password
```bash
POST /api/forgot-password
Content-Type: application/json

{
    "email": "john@example.com"
}
```

Response:
```json
{
    "status": true,
    "message": "Password reset link sent to your email"
}
```

### Logout
```bash
POST /api/logout
Authorization: Bearer {token}
```

## 🛠️ Development

### Running in Development Mode

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite for hot reloading
npm run dev
```

### Building for Production

```bash
npm run build
php artisan optimize
```

### Available Artisan Commands

```bash
# Create admin user
php artisan admin:create

# List all admin users
php artisan admin:list
```

## 📁 Project Structure

```
laravel-boilerplate/
├── app/
│   ├── Console/
│   │   └── Commands/          # Custom artisan commands
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/         # Admin panel controllers
│   │   │   ├── API/           # API controllers
│   │   │   ├── Auth/          # Authentication controllers
│   │   │   └── User/          # User panel controllers
│   │   └── Middleware/        # Custom middleware
│   └── Models/
│       ├── System/            # Role, Permission models
│       └── Users/             # User model
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/              # Database seeders
├── resources/
│   ├── views/
│   │   ├── admin/            # Admin panel views
│   │   ├── auth/             # Authentication views
│   │   ├── layouts/          # Layout templates
│   │   └── user/             # User panel views
│   └── css/
│       └── app.css           # Tailwind CSS
├── routes/
│   ├── web.php               # Web routes
│   └── api.php               # API routes
└── .env.example              # Environment variables template
```

## 🎨 Color Theme

The boilerplate uses a modern gradient color scheme:

- **Primary:** Indigo to Purple gradient (#6366f1 → #8b5cf6)
- **Accent Colors:** Blue, Cyan, Teal, Pink gradients
- **Background:** Light gradient (indigo-50 → purple-50 → blue-50)

All views are styled consistently with this theme.

## 🔒 Security Features

- Password hashing with bcrypt
- CSRF protection
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade templating)
- Role-based access control (RBAC)
- API authentication with Sanctum
- Soft deletes for data retention

## 📝 License

This boilerplate is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📧 Support

If you have any questions or need help, please open an issue on GitHub.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API authentication

---

**Made with ❤️ for the Laravel community**
