# **Simple Human Resource Management System (HRMS) on AWS**
CCS6334 Database and Cloud Security (Trimester October/November 2024 - Term 2430)

## **Overview**
<div align='justify'>
This project is a simple Web-Based Human Resource Management System (HRMS) designed to manage employee records efficiently and securely. The system considered three user roles which include <strong>HR Admin</strong>, <strong>Manager</strong>, and <strong>Employee</strong>, where each of them has distinct levels of access and functionalities. For this HRMS, key business requirements involve <strong>scalability</strong> to handle a growing number of employees and departments, <strong>security and compliance</strong> where data must be protected with access control and encryption, <strong>reliability</strong> with minimal downtime, and <strong>remote accessibility</strong> where employees and HR personnel need secure access from different locations. Therefore, to meet these requirements, <strong>AWS services</strong> were selected to enhance security, performance, and scalability. The system includes:
</div>

### **User Roles & Permissions**
- **HR Admin:** Full access to employee records and role management.
- **Manager:** Limited access to employees' information within their department.
- **Employee:** Can view and manage their own profile.

### **Key Features**
- ✅ **Employee Management** – Create, update, and delete employee records.
- ✅ **Role-Based Access Control (RBAC)** – Ensures restricted access based on user roles.
- ✅ **Secure Database Integration** – Apply encryption and auditing for sensitive data.
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
- Install XAMPP (Includes Apache, PHP, and MySQL) from [HERE](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe).
- Install Composer from [HERE](https://getcomposer.org/download/) for PHP dependency management.

**2. Database Setup:**
- Install the MySQL Workbench from [HERE](https://dev.mysql.com/downloads/file/?id=536668).
- Run the provided SQL script (can be seen in the SQL_Script PDF file) in this project directory to set up the schema.

### **📌 Installation Steps**

**Step 1: Navigate to the Web Server Directory**
```bash
cd C:\xampp\htdocs
```

**Step 2: Clone the Repository**
```bash
git clone https://github.com/kaijun05/simple-hrms.git
```
   
**Step 3: Navigate to the Project Directory**
```bash
cd simple-hrms
```
**Note**: Ensure that it points to the correct `simple-hrms` with all the respective files.

**Step 4: Install Dependencies**
```bash
composer install
```

**Step 5: Open the Project in VSCode (or any other IDEs)**

**Step 6: Follow the Amazon Services Setup PDF file attached in this project directory to setup the Amazon services and environment required to host the HRMS.**

**Note: Setting Up Environment Variables**
- Rename `.env.example` to `.env`.
- Open `.env` and update the database credentials:

  ```bash
  DB_HOST=your-rds-database-endpoint
  DB_DATABASE=HRMS
  DB_USERNAME=your-rds-master-username
  DB_PASSWORD=your-rds-master-password
  ```

**Step 7: Start the Web Server**
- Access the application via:

  ```bash
  http://<public_ipv4_dns>/src/login.php
  ```
