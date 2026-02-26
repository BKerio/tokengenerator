# Tokenpap system

This Laravel application provides authentication services for the webclient.

## Features
- User registration
- User login/logout
- Password reset functionality
- Email verification
- Session management

## Technology Stack
- Laravel 12
- PHP 8.2
- MongoDB

## Setup
1. Install dependencies: `composer install`
2. Configure `.env` file with MongoDB connection
3. Run migrations: `php artisan migrate`
4. Start server: `php artisan serve`

## API Endpoints
All authentication endpoints are provided by Laravel's built-in authentication system:
- POST `/register` - User registration
- POST `/login` - User login
- POST `/logout` - User logout
- POST `/password/email` - Send password reset link
- POST `/password/reset` - Reset password
