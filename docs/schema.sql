-- CashGuard PostgreSQL schema (generated from the SDD data dictionary).
-- In the Django project these tables are created by migrations; this file documents the same constraints.

CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(128) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    gender VARCHAR(10) NOT NULL CHECK (gender IN ('MALE','FEMALE','OTHER')),
    account_type VARCHAR(10) NOT NULL DEFAULT 'STUDENT' CHECK (account_type IN ('STUDENT','EMPLOYEE','ADMIN')),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_login TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE student_profiles (
    profile_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL UNIQUE REFERENCES users(user_id) ON DELETE CASCADE,
    program VARCHAR(10) NOT NULL CHECK (program IN ('BSIT','BSCS','BSIS','BSEMC')),
    year_level SMALLINT NOT NULL CHECK (year_level BETWEEN 1 AND 4)
);

CREATE TABLE login_attempts (
    attempt_id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(user_id) ON DELETE SET NULL,
    username_entered VARCHAR(50) NOT NULL,
    was_successful BOOLEAN NOT NULL,
    ip_address INET,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    category_id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE NULLS NOT DISTINCT (user_id, name)
);

CREATE TABLE expenses (
    expense_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    category_id INTEGER NOT NULL REFERENCES categories(category_id) ON DELETE NO ACTION DEFERRABLE INITIALLY DEFERRED,
    description VARCHAR(255) NOT NULL,
    amount NUMERIC(12,2) NOT NULL CHECK (amount > 0),
    expense_date DATE NOT NULL,
    payment_method VARCHAR(10) NOT NULL DEFAULT 'CASH' CHECK (payment_method IN ('CASH','GCASH','MAYA','CARD','BANK','OTHER')),
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE incomes (
    income_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    source VARCHAR(100) NOT NULL,
    income_type VARCHAR(12) NOT NULL CHECK (income_type IN ('ALLOWANCE','SALARY','SCHOLARSHIP','PART_TIME','GIFT','OTHER')),
    amount NUMERIC(12,2) NOT NULL CHECK (amount > 0),
    income_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE budgets (
    budget_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    budget_month SMALLINT NOT NULL CHECK (budget_month BETWEEN 1 AND 12),
    budget_year SMALLINT NOT NULL CHECK (budget_year BETWEEN 2000 AND 2100),
    total_amount NUMERIC(12,2) NOT NULL CHECK (total_amount > 0),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, budget_month, budget_year)
);

CREATE TABLE budget_allocations (
    allocation_id SERIAL PRIMARY KEY,
    budget_id INTEGER NOT NULL REFERENCES budgets(budget_id) ON DELETE CASCADE,
    category_id INTEGER NOT NULL REFERENCES categories(category_id) ON DELETE NO ACTION DEFERRABLE INITIALLY DEFERRED,
    allocated_amount NUMERIC(12,2) NOT NULL CHECK (allocated_amount > 0),
    UNIQUE (budget_id, category_id)
);

CREATE TABLE savings_goals (
    goal_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    goal_name VARCHAR(100) NOT NULL,
    target_amount NUMERIC(12,2) NOT NULL CHECK (target_amount > 0),
    start_date DATE NOT NULL DEFAULT CURRENT_DATE,
    target_date DATE NOT NULL,
    status VARCHAR(10) NOT NULL DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE','COMPLETED','CANCELLED')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, goal_name),
    CHECK (target_date > start_date)
);

CREATE TABLE goal_contributions (
    contribution_id SERIAL PRIMARY KEY,
    goal_id INTEGER NOT NULL REFERENCES savings_goals(goal_id) ON DELETE CASCADE,
    contribution_type VARCHAR(10) NOT NULL DEFAULT 'DEPOSIT' CHECK (contribution_type IN ('DEPOSIT','WITHDRAWAL')),
    amount NUMERIC(12,2) NOT NULL CHECK (amount > 0),
    contribution_date DATE NOT NULL,
    notes VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
