# CCWLCS - Centralized Clinic, Ward & Laboratory Coordination System

Group 14 · SCS2202 / IS2102 · UCSC
Plain PHP, MySQL, HTML, CSS and vanilla JavaScript on XAMPP. MVC, no frameworks or libraries.

## Requirements
- XAMPP with **PHP 8.0 or newer**
- A browser

## Setup
1. Put this folder at `C:\xampp\htdocs\ccwlcs`
2. Copy `config/config.example.php` to `config/config.php` (edit the DB password if yours isn't empty)
3. Start Apache and MySQL in XAMPP
4. phpMyAdmin -> Import -> `sql/schema.sql`
5. Open `http://localhost/ccwlcs/install.php` once (creates the 15 accounts), then delete it
6. Optional: open `http://localhost/ccwlcs/seed_demo.php` once for demo data, then delete it
7. Log in at `http://localhost/ccwlcs/`

Already had the database from before? Run `sql/migrations/001_add_missing_columns.sql` instead of re-importing.

## Accounts (password: pass123)
| Username | Role |
|---|---|
| admin | Administrator |
| reception1 | Receptionist |
| opd1 | OPD Doctor |
| clinic_sur / clinic_med / clinic_ped | Clinic Doctor (consultant) |
| ward_sur / ward_med / ward_ped | Ward Doctor (IMO) |
| nurse_sur / nurse_med / nurse_ped | Ward Nurse |
| lab1 | Lab Personnel |
| rad1 | Radiology Personnel |
| pharm1 | Pharmacist |

Demo password only - a real deployment would use individual passwords.

## Team
| Member | Modules |
|---|---|
| Praveen | Registration & OPD |
| Chamethya | Clinic & Ward |
| Mandira | Laboratory, Radiology & Notifications |
| Shaveena | Drug Dispensary & Administration |
