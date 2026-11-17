# Online Kids Birthday Party Planning System

## Student Information
- Name: UWIKUNZE JUDITH
- Registration Number: 224007430
- Project Title: Online Kids Birthday Party Planning System

## 1. Project Description
This system helps parents plan birthday parties for their children.
Parents can create parties, view vendors and book services such as cakes,
decorations and venues.

## 2. Technologies Used
- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- PDO
- Fetch API and JSON

## 3. Database Setup
1. Start Apache and MySQL using XAMPP.
2. Open phpMyAdmin.
3. Create a database named kids_party_planner.
4. Import schema.sql.
5. Import seed.sql.

## 4. Database Configuration
Database name: kids_party_planner
Host: localhost
Username: root
Password: (use your local MySQL password)

## 5. How to Run the Project
1. Copy the project folder into htdocs.
2. Start Apache and MySQL.
3. Open your browser.
4. Visit:
   http://localhost/Online_Kids_Birthday_Planning_System/

## 6. Main Features
- User registration and login.
- Password hashing.
- Create, view, update and delete birthday parties.
- Create, view, update and delete vendors.
- Create and view bookings.
- Delete bookings.
- Search vendors using Fetch API.
- Session management.
- CSRF protection.

## 7. Main Routes
- index.php?page=home
- index.php?page=register
- index.php?page=login
- index.php?page=dashboard
- index.php?page=parties
- index.php?page=vendors
- index.php?page=bookings

## 8. Security
- Passwords are protected using password_hash() and password_verify().
- PDO prepared statements are used.
- htmlspecialchars() helps prevent XSS.
- CSRF tokens protect POST forms.
- Server-side validation is implemented.

## 9. Testing Evidence
The following features have been tested:
- User registration.
- User login.
- Party creation.
- Party editing.
- Party deletion.
- Vendor creation.
- Vendor editing.
- Vendor deletion.
- Booking creation.
- Booking deletion.
- Vendor live search.

## 10. Conclusion
The system provides a simple way for parents to organize
children's birthday parties and book different vendors.