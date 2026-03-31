## Event Management System (PHP + MySQL + Flask)

This is a complete Event Management System project for XAMPP (Apache + MySQL) using:
- PHP + MySQL (auth, events, bookings, admin panel, Razorpay payment update)
- Python (Flask) for chatbot + QR ticket generation
- Bootstrap for responsive UI

---

## 1) Requirements

### Software
- XAMPP (Apache + MySQL)
- PHP 8.x (comes with XAMPP)
- Python 3.10+ (any recent Python 3 works)

### Python packages
Install once:

```bash
pip install flask flask-cors qrcode pillow
```

---

## 2) Setup (XAMPP)

1. Copy the folder `event_management_system` into your XAMPP web root:
   - `C:\xampp\htdocs\event_management_system`

2. Start **Apache** and **MySQL** from XAMPP Control Panel.

3. Create database and tables:
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create DB: `event_db`
   - Import: `database.sql` (inside this project)

4. Configure PHP DB connection:
   - Edit `config.php` if your MySQL username/password differ.

---

## 3) Razorpay Setup (Test Mode)

This project includes Razorpay integration in **test mode**.

1. Create a Razorpay account and get:
   - Key ID
   - Key Secret

2. Set them in:
   - `config.php` (`RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`)

Notes:
- Payment is handled by Razorpay Checkout on the frontend.
- After payment, the PHP endpoint verifies signature and marks booking as `paid`.

---

## 4) Run the Flask Service (Chatbot + QR)

From the project folder:

```bash
cd C:\xampp\htdocs\event_management_system
python chatbot.py
```

It runs on:
- `http://127.0.0.1:5000`

---

## 5) Run the website

Open:
- `http://localhost/event_management_system/`

### Accounts
- Register a new user (role defaults to `user`)
- To create an admin, in phpMyAdmin set the user's role to `admin`

---

## 6) Folder structure

- `config.php` DB + site config
- `register.php`, `login.php`, `logout.php`
- `index.php` events listing + search/filter + chatbot widget
- `book.php` create booking + payment button
- `payment_verify.php` Razorpay verify + mark booking paid
- `my_bookings.php` booking history + QR ticket links
- `admin_dashboard.php` admin stats + bookings
- `add_event.php` add/edit/delete events
- `chatbot.py` Flask API (chat + QR generator)
- `qr.py` QR helper
- `assets/` CSS + JS

