# **Human Resource Management System (HRMS) on AWS**

## **Overview**

This project is a web-based Human Resource Management System (HRMS) designed to manage employee records efficiently and securely. The system includes:

### **User Roles & Permissions**
- **HR Admin:** Full access to employee records and role management.
- **Manager:** Limited access to employees in their department.
- **Employee:** Can view and manage their own profile.

### **Key Features**
- ✅ **Employee Management** – Create, update, and delete employee records.
- ✅ **Role-Based Access Control (RBAC)** – Ensures restricted access based on user roles.
- ✅ **Secure Database Integration** – Uses encryption and auditing for sensitive data.
- ✅ **Self-Service Portal** – Employees can update their personal details.

## **Technology Stack**
| Category | Technology |
| --- | --- |
| **Programming Language** | PHP 8.2 |
| **Database** | MySQL (Amazon RDS) |
| **Front-End** | HTML, CSS, and JavaScript
| **Back-End Framework** | PHP |
| **Environment Variables** | Managed with `phpdotenv` for secure configuration. |
| **Web Server** | Apache (EC2) |
| **Operating System** | Amazon Linux 2 (AMI) (HVM) (EC2 Instance) |

## **Installation and Setup**
### 📌 **Prerequisites**
**1. Software Requirements:**
- Install XAMPP (Includes Apache, PHP, and MySQL) from https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe.
- Install Composer from https://getcomposer.org/download/ for PHP dependency management.

**2. Database Setup:**
- Install the MySQL Workbench from https://dev.mysql.com/downloads/file/?id=536668.
- Run the provided SQL script (found in the PDF file) in the CCS6334-Assignment-2-Group-5 zip folder to set up the schema.

### **📌 Installation Steps**

**Step 1: Navigate to the Web Server Directory**
```bash
cd C:\xampp\htdocs
```

**Step 2: Clone the Repository**
```bash
git clone https://github.com/kaijun1105/CCS6344-Assignment-2-Group-5.git
```
   
**Step 3: Navigate to the Project Directory**
```bash
cd CCS6344-Assignment-2-Group-5
```

**Step 4: Install Dependencies**
```bash
composer install
```

**Step 5: Open the Project in VSCode**

**Step 6: Set Up Environment Variables**
- Rename `.env.example` to `.env`.
- Open `.env` and update the database credentials:
  
  ```bash
  DB_HOST=your-rds-database-endpoint
  DB_DATABASE=HRMS
  DB_USERNAME=your-rds-master-username
  DB_PASSWORD=your-rds-master-password
  ```

**Step 7: Start the Web Server**
- Start Apache using XAMPP.
- Access the application via:
  
  ```bash
  http://localhost/CCS6344-Assignment-2-Group-5/src/login.php
  ```
