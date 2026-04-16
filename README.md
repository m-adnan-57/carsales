# 🚗 CarSales – React SPA + PHP REST API

A full-stack car sales Single Page Application built with React (frontend)
and PHP + MySQL (backend), designed for deployment on mi-linux.wlv.ac.uk.

---

## 📁 Project Structure

```
carsales/
├── frontend/               ← React SPA (Vite)
│   ├── src/
│   │   ├── App.jsx         ← All components & pages
│   │   └── main.jsx        ← Entry point
│   ├── index.html
│   ├── package.json
│   └── vite.config.js
│
├── backend/                ← PHP REST API
│   ├── index.php           ← Router
│   ├── .htaccess           ← URL rewriting
│   ├── config/
│   │   └── database.php    ← DB connection
│   ├── middleware/
│   │   └── auth.php        ← JWT auth + helpers
│   ├── api/
│   │   ├── cars.php        ← /cars endpoints
│   │   ├── auth.php        ← /login + /register
│   │   ├── favourites.php  ← /favourites endpoints
│   │   └── messages.php    ← /messages endpoints
│   └── database/
│       └── schema.sql      ← DB setup script
│
└── README.md
```

---

## 🚀 Setup & Deployment

### Step 1 – Database (mi-linux)

SSH into mi-linux and run:

```bash
mysql -u YOUR_USER -p YOUR_DB < backend/database/schema.sql
```

Then edit `backend/config/database.php` with your actual credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cm1234_carsales');   // your DB
define('DB_USER', 'cm1234');            // your username
define('DB_PASS', 'your_password');
```

### Step 2 – Upload Backend to mi-linux

Upload the entire `backend/` folder to:
```
~/public_html/api/
```

Your API will then be at:
```
https://mi-linux.wlv.ac.uk/~cm1234/api/cars
```

### Step 3 – Build React Frontend

```bash
cd frontend
npm install
npm run build
```

Copy the `dist/` folder contents to:
```
~/public_html/
```

### Step 4 – Update API Base URL

In `frontend/src/App.jsx`, line 5:
```js
const API_BASE = "https://mi-linux.wlv.ac.uk/~YOUR_USERNAME/api";
```

---

## 🔌 REST API Endpoints

| Method | Endpoint          | Auth     | Description            |
|--------|-------------------|----------|------------------------|
| GET    | /cars             | Public   | List all cars          |
| GET    | /cars/{id}        | Public   | Get car details        |
| POST   | /cars             | Admin    | Add new car            |
| PUT    | /cars/{id}        | Admin    | Update car             |
| DELETE | /cars/{id}        | Admin    | Delete car             |
| POST   | /register         | Public   | Register user          |
| POST   | /login            | Public   | Login → returns JWT    |
| GET    | /favourites       | User     | Get saved cars         |
| POST   | /favourites       | User     | Save a car             |
| DELETE | /favourites/{id}  | User     | Unsave a car           |
| GET    | /messages         | User     | Get messages           |
| POST   | /messages         | User     | Send message to seller |

### Query Parameters (GET /cars)
- `?brand=BMW` – filter by brand
- `?model=3+Series` – filter by model
- `?min_price=5000` – minimum price
- `?max_price=20000` – maximum price
- `?year=2020` – filter by year

---

## 🔐 Authentication

JWT tokens are used. After login, include:
```
Authorization: Bearer <token>
```

Passwords are hashed with `password_hash($pass, PASSWORD_BCRYPT, ['cost'=>12])`.

### Default Admin Account
- Email: `admin@carsales.com`
- Password: `Admin1234!`

⚠️ Change this immediately after first login.

---

## 🧪 Testing (Task 2)

### API Testing with Postman

1. Import collection from Postman
2. Set base URL variable: `https://mi-linux.wlv.ac.uk/~cm1234/api`
3. Test endpoints:
   - GET /cars → expect 200 + array
   - POST /login (body: email/password) → expect token
   - POST /cars (with Bearer token) → expect 201
   - DELETE /cars/1 (without token) → expect 401

### Frontend Testing

Using React Testing Library + Vitest:
```bash
npm install --save-dev vitest @testing-library/react @testing-library/jest-dom
```

Test the CarCard component renders car data correctly.
Test the search form updates state and calls the API.

---

## ✅ Feature Checklist

### User Side
- [x] View all cars with images, specs, price
- [x] Search by brand, min/max price
- [x] View full car details in modal
- [x] Register & login
- [x] Save favourite cars
- [x] Contact seller via message

### Admin Side
- [x] Add new car listing
- [x] Edit existing car
- [x] Delete car
- [x] Upload image URL
- [x] View all incoming messages

### Technical
- [x] SPA – no page reloads
- [x] REST API (GET, POST, PUT, DELETE)
- [x] JWT authentication
- [x] bcrypt password hashing
- [x] SQL injection prevention (PDO prepared statements)
- [x] CORS headers
- [x] Form validation
- [x] Error handling

---

## 📦 Git Workflow

```bash
git init
git add .
git commit -m "Initial project structure"

# Weekly commits:
git add .
git commit -m "Add car search filtering"
git commit -m "Implement JWT auth"
git commit -m "Add favourites feature"
git commit -m "Admin CRUD panel"
git push origin main
```

---

## 📚 Discussion Points (Task 2 – 2000 words)

Compare:

**Traditional PHP Website**
- Server renders full HTML on every request
- Page reloads on navigation
- Tightly coupled frontend/backend
- Slower perceived performance

**SPA (React + REST API)**
- Frontend decoupled from backend
- Dynamic updates without reload
- JSON API can serve mobile apps too
- Better UX, faster after initial load
- Cacheable API responses

References: Fielding (2000) REST dissertation, React docs, MDN SPA docs.
