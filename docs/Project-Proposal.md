# CashGuard – Project Proposal (IM2 Project Intake form)

Copy each section into the matching field of the Project Intake form. Enter the specific objectives one at a time with **Add objective** and leave out the numbers, because the form numbers them.

---

## OVERVIEW

### Project Title
CashGuard: A Web-Based Personal Finance Management System for College Students and Campus Employees

### Proposed Technology / Platform
Python with Django (full-stack web: Django templates, HTML/CSS, Chart.js for charts) and a PostgreSQL database (Supabase-hosted PostgreSQL may be used for deployment). Web-based, accessed through a browser.

### Target Users / Beneficiaries
- **Students:** college students living on a weekly or monthly allowance. They record their expenses and income, set a monthly budget per category, track savings goals, and view their own reports.
- **Employees:** campus staff and faculty. They use the same personal finance features as students to manage their salary and spending.
- **Administrators:** the system administrator. They manage user accounts and the default expense categories, monitor login activity, and view campus-wide spending summaries.

### Project Description
CashGuard is a web application where each user keeps a complete record of their personal finances in one PostgreSQL database. Users record every expense with its category, amount, date, and payment method (cash, GCash, Maya, card), and every income such as allowance, salary, or scholarship. At the start of each month, users set a budget and divide it among categories such as Food, Transportation, and School Supplies. CashGuard compares each category's allocation with the actual expenses recorded for that month and shows how much is left. Users also create savings goals, for example "New Laptop – ₱30,000 by March", and log each deposit and withdrawal. The system computes the amount saved and marks the goal as completed when the target is reached.

All records can be added, viewed, edited, deleted, searched, and filtered by category, type, payment method, or date range. CashGuard produces reports from the stored data: budget versus actual spending per category, monthly income versus expenses, and savings goal progress. Administrators get campus-wide summaries such as total spending per month, spending per category, and average spending per account type and program. Database constraints keep the data valid: no negative amounts, one budget per month per user, and no deleting a category that is still in use. Users therefore no longer need to track their money in notebooks and scattered e-wallet histories.

---

## PROBLEM & OBJECTIVES

### Problem Statement
College students and campus employees usually track their money through memory, paper notes, spreadsheets, or the transaction history of each e-wallet. Students on a fixed allowance often run out of money before their next allowance and cannot tell where it went, because their spending is split between cash, GCash or Maya, and cards. No single record shows all of their spending.

These manual methods do not check the data and cannot summarize it. Amounts are mistyped or recorded twice, a monthly budget is planned but never compared with actual spending, and savings are tracked as a single number that gets overwritten instead of a history of deposits. Answering a simple question such as "How much did I spend on food this month compared with my budget?" requires adding up entries by hand, so it is rarely done, and overspending is noticed only when the money is gone.

### General Objective
To develop a database-driven web application that will record, organize, and summarize the expenses, income, budgets, and savings goals of college students and campus employees.

### Specific Objectives (enter one per "Add objective")
- To allow users to create, view, update, and delete their expense, income, budget, and savings goal records.
- To allow users to divide each monthly budget into category allocations and compare them with the actual expenses of that month.
- To track the progress of savings goals through recorded deposits and withdrawals.
- To allow users to search and filter records by keyword, category, payment method, type, and date range.
- To generate reports such as budget versus actual spending per category, monthly income versus expenses, and savings goal progress.
- To provide administrators with campus-wide summaries of spending per month, per category, and per account type.
- To provide user accounts with Student, Employee, and Administrator roles, including password management and a temporary lockout after repeated failed logins.
- To enforce data integrity through primary key, foreign key, UNIQUE, CHECK, and NOT NULL constraints in a PostgreSQL database.

---

## SCOPE

### Scope
**IN SCOPE**
- User roles: Student, Employee, Administrator.
- Account module: registration (with program and year level for students), login and logout, profile update, password change, and lockout after 3 failed login attempts.
- Expense module: CRUD with search by description and filters for category, payment method, and date range.
- Income module: CRUD with filters for income type and date range.
- Category module: default categories managed by the admin, plus each user's own categories (deactivated instead of deleted when in use).
- Budget module: one budget per user per month, divided into category allocations, with the remaining amount computed from expenses.
- Savings goal module: goals with target amount and target date, a deposit and withdrawal history, and automatic completion.
- Reports: personal dashboard, budget vs. actual per category, monthly income vs. expenses, goal progress; admin summaries of spending per month, category, account type, and program, plus user statistics.
- Admin module: user search, filtering, deactivation, and deletion; default category management; login activity log.

**OUT OF SCOPE / LIMITATIONS**
- No connection to banks, GCash, Maya, or other e-wallets; all records are entered manually.
- No online payments or money transfers.
- No email, SMS, or push notifications.
- No mobile app and no offline mode; the system requires an internet connection and a browser.
- No AI or automated financial advice.
- Single currency only (Philippine peso); English only.
- Budgets are monthly only; weekly or custom periods are not supported.
- Administrators can view records for monitoring but cannot edit a user's financial records.
