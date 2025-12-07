# Smart Booking System

The **Smart Booking System** is a complete full-stack application built with **Laravel (Backend API)** and **Vue 3 + Vite (Frontend SPA)**.  
It provides a streamlined solution for managing bookings, customers, schedules, and related workflows with a modern, responsive interface.

## 🚀 Project Overview

This repository contains both the backend API (Laravel) and the frontend application (Vue 3), organized as:

```
smart-booking-system/
│
├── smart-booking-backend/      # Laravel REST API
└── smart-booking-frontend/     # Vue 3 SPA (Vite)
```

The backend handles all business logic and data operations, while the frontend provides a clean, user-friendly UI for interacting with the system.

## ✨ Features

### ✔ Booking Management  
Create, view, update, and manage booking records.

### ✔ Customer Management  
Store and manage customer details linked to bookings.

### ✔ Modern Frontend UI  
Vue 3 + Vite powered SPA with fast performance.

### ✔ Secure REST API  
Built using Laravel with authentication-ready structure.

### ✔ API + SPA Architecture  
Backend and frontend are fully decoupled for modular development and deployment.

## 🧩 Tech Stack

### Frontend
- Vue 3 (Composition API)
- Pinia
- Vue Router
- Vite
- Axios

### Backend
- Laravel
- MySQL / MariaDB
- Eloquent ORM
- Laravel artisan tools

# 1️⃣ Backend Setup (Laravel API)

## Requirements
- PHP 8.1+
- Composer
- MySQL
- Node.js (optional for asset builds)

## Installation
```bash
cd smart-booking-backend
composer install
cp .env.example .env
php artisan key:generate
```

## Database Configuration
Edit `.env`:
```
DB_DATABASE=smart_booking
DB_USERNAME=root
DB_PASSWORD=
```

## Run Migrations
```bash
php artisan migrate
```

## Start Backend Server
```bash
php artisan serve
```

Backend will run at:

**http://127.0.0.1:8000**

# 2️⃣ Frontend Setup (Vue 3 + Vite)

## Requirements
- Node.js 20+

## Installation
```bash
cd smart-booking-frontend
npm install
```

## Configure API URL
Update `.env` or Axios config:
```
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

## Start Development Server
```bash
npm run dev
```

Default frontend URL:

**http://localhost:5173**

## 🔗 Connecting Frontend & Backend

The frontend communicates with Laravel via Axios using routes like:

```
/api/bookings
/api/customers
```

Make sure CORS is enabled in the backend (`config/cors.php` or middleware).

## 📦 Production Build

### Frontend
```bash
npm run build
```

### Backend
```bash
php artisan optimize
```

## 📁 Repository Structure

```
smart-booking-system/
│
├── smart-booking-backend/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── routes/
│   ├── database/
│   └── public/
│
└── smart-booking-frontend/
    ├── src/
    ├── public/
    └── package.json
```

## 🧪 Testing

Run Laravel backend tests:
```bash
php artisan test
```

## 🤝 Contributing

Contributions are welcome!  
Please follow:

- **PSR-12** for PHP (backend)
- **ESLint + Prettier** conventions (frontend)

## 📜 License

This project is licensed under the **MIT License**.