# FamiliarFaceWeb

**FamiliarFaceWeb** is a Laravel-based web application that integrates with the [Familiar-Face](https://github.com/Hadoroo/Familiar-Face) API to provide secure **2-Factor Authentication (2FA)** using real-time **face recognition**.

This project allows users to log in using traditional credentials (email & password) and then perform **live face verification** through their device's webcam for additional security.

---

## 🔧 Features

- User authentication with face recognition as 2FA
- Integration with Familiar-Face API (face registration, recognition, and deletion)
- Webcam-based real-time face capture
- Clean Laravel UI with authentication flow
- Easy API configuration

---

## 🧠 How It Works

1. Users log in with their email and password.
2. The system requests access to the user's webcam.
3. A photo is captured and sent to the Familiar-Face API for verification.
4. If the face matches the registered data, access is granted.

---

## 📦 Requirements

- PHP >= 8.0
- Laravel >= 9
- Composer
- Familiar-Face API Server (see below)
- Web browser with webcam support

---

## 🚀 Getting Started

### 1. Clone this repository

```bash
git clone https://github.com/aryayudh06/FamiliarFaceWeb.git
cd FamiliarFaceWeb
````

### 2. Install dependencies

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Set up `.env`

Configure your database and the Familiar-Face API endpoint:

```dotenv
API_BASE_URL=http://localhost:8000  # Adjust this to where your Familiar-Face API server is running
```

### 4. Run migrations and serve

```bash
php artisan migrate
php artisan serve
```

---

## 🔌 Setting Up the Face Recognition API

To use this web app, you need to have the [Familiar-Face](https://github.com/Hadoroo/Familiar-Face) API server running locally or on a remote server.

### Steps:

```bash
git clone https://github.com/Hadoroo/Familiar-Face.git
cd Familiar-Face
pip install -r requirements.txt
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

> Make sure the API server is accessible from your Laravel web app. Update the API URL accordingly.

---

## 📄 License

This project is licensed under the MIT License.

---

## 🤝 Credits

* Backend API: [Familiar-Face by our team (Hadoroo, Arya and co.)](https://github.com/Hadoroo/Familiar-Face)
* Frontend: Laravel-based face recognition auth system by [aryayudh06](https://github.com/aryayudh06)

