# 🧑‍💼 KormoShathi — HR Management System

A full-featured HR Management System built with plain PHP, designed for small to mid-sized organizations to manage employees, attendance, payroll, leave, recruitment, and performance — all in one place.

🔗 **Live Demo:** [hr-sysytem.onrender.com](https://hr-sysytem.onrender.com)

---

## ✨ Features

- 👥 **Employee Management** — Add, update, and manage employee profiles with photo upload
- 🕒 **Attendance Tracking** — Daily attendance logging and reporting
- 💰 **Payroll Management** — Salary structure, deductions, and downloadable payslips
- 🌴 **Leave Management** — Leave requests, approvals, and tracking
- 🤖 **AI Resume Screening** — Automatic candidate screening and scoring using Groq AI (Llama 3.3)
- 📅 **Interview Scheduling** — Shortlist candidates and send interview invitations via email
- 📋 **Job Postings & Applications** — Manage open positions and candidate submissions
- 📊 **Performance Reviews** — Track and record employee performance
- 📢 **Notice Board** — Company-wide notices and notifications
- 🪪 **Employee ID Cards** — Generate digital ID cards
- 📧 **Automated Emails** — Confirmation and interview call emails via Brevo API

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2 |
| Database | MySQL 8.4 (Aiven) |
| Email | Brevo API |
| Image Storage | Cloudinary |
| AI Screening | Groq API (Llama 3.3 70B) |
| PDF Parsing | smalot/pdfparser |
| Deployment | Docker + Render |
| Frontend | Bootstrap 5, Vanilla JS |

---

## 🚀 Deployment

This project is containerized with Docker and deployed on [Render](https://render.com).

### Environment Variables Required
### Local Setup

```bash
git clone https://github.com/ProTiger24/HR_Sysytem.git
cd HR_Sysytem
composer install
cp .env.example .env   # fill in your credentials
```

Import the database schema:

```bash
mysql -h <host> -P <port> -u <user> -p <database> < database.sql
```

Run locally with XAMPP/LAMPP or PHP's built-in server:

```bash
php -S localhost:8000
```

### Docker

```bash
docker build -t kormoshathi .
docker run -p 80:80 --env-file .env kormoshathi
```

---

## 📁 Project Structure
---

## 🔒 Security Notes

- All secrets are managed via environment variables, never committed to the repository
- File uploads (profile pictures) are stored on Cloudinary, not the local filesystem, for persistence across deployments
- Emails are sent via Brevo's HTTPS API (not SMTP) for compatibility with hosting providers that restrict outbound SMTP ports

---

## 📄 License

This project is developed for internal/educational use as part of a CodeAlpha internship project.

---

## 👨‍💻 Author

**Abdul Alim**
CSE Student, BUBT | Competitive Programmer
GitHub: [@ProTiger24](https://github.com/ProTiger24)
