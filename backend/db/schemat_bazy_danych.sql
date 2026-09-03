

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE users (
    id          CHAR(36)     NOT NULL DEFAULT (UUID()),
    name        VARCHAR(100) NOT NULL,
    surname     VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    password    VARCHAR(255) NOT NULL,          -- hash hasła (np. bcrypt)
    phone       VARCHAR(20)  NULL,
    role        ENUM('client', 'employee', 'admin') NOT NULL DEFAULT 'client',
    active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE service_categories (
    id          CHAR(36)     NOT NULL DEFAULT (UUID()),
    name        VARCHAR(100) NOT NULL,
    description TEXT         NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_service_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE services (
    id          CHAR(36)       NOT NULL DEFAULT (UUID()),
    category_id CHAR(36)       NOT NULL,
    name        VARCHAR(150)   NOT NULL,
    description TEXT           NULL,
    duration    SMALLINT UNSIGNED NOT NULL,      
    price       DECIMAL(10,2)  NOT NULL,
    active      TINYINT(1)     NOT NULL DEFAULT 1,

    PRIMARY KEY (id),
    KEY idx_services_category (category_id),
    CONSTRAINT fk_services_category
        FOREIGN KEY (category_id) REFERENCES service_categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE employees (
    id          CHAR(36)    NOT NULL DEFAULT (UUID()),
    user_id     CHAR(36)    NOT NULL,
    description TEXT        NULL,
    active      TINYINT(1)  NOT NULL DEFAULT 1,

    PRIMARY KEY (id),
    UNIQUE KEY uq_employees_user (user_id),      
    CONSTRAINT fk_employees_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE employee_services (
    employee_id CHAR(36) NOT NULL,
    service_id  CHAR(36) NOT NULL,

    PRIMARY KEY (employee_id, service_id),
    KEY idx_employee_services_service (service_id),
    CONSTRAINT fk_empserv_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_empserv_service
        FOREIGN KEY (service_id) REFERENCES services(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE employee_availability (
    id            CHAR(36)          NOT NULL DEFAULT (UUID()),
    employee_id   CHAR(36)          NOT NULL,
    day_of_week   TINYINT UNSIGNED  NULL,        
    specific_date DATE              NULL,        
    start_time    TIME              NOT NULL,
    end_time      TIME              NOT NULL,

    PRIMARY KEY (id),
    KEY idx_availability_employee (employee_id),
    CONSTRAINT fk_availability_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_availability_time
        CHECK (start_time < end_time),
    CONSTRAINT chk_availability_day_xor_date
        CHECK (
            (day_of_week IS NOT NULL AND specific_date IS NULL) OR
            (day_of_week IS NULL AND specific_date IS NOT NULL)
        ),
    CONSTRAINT chk_availability_day_range
        CHECK (day_of_week IS NULL OR day_of_week BETWEEN 1 AND 7)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE reservations (
    id                CHAR(36)      NOT NULL DEFAULT (UUID()),
    user_id           CHAR(36)      NOT NULL,   
    employee_id       CHAR(36)      NOT NULL,
    service_id        CHAR(36)      NOT NULL,
    reservation_date  DATE          NOT NULL,
    start_time        TIME          NOT NULL,
    end_time          TIME          NOT NULL,
    status            ENUM('pending', 'confirmed', 'completed', 'cancelled')
                                    NOT NULL DEFAULT 'pending',
    comment           TEXT          NULL,
    created_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_reservations_user (user_id),
    KEY idx_reservations_employee (employee_id),
    KEY idx_reservations_service (service_id),
    KEY idx_reservations_date (reservation_date),
    KEY idx_reservations_employee_slot (employee_id, reservation_date, start_time),

    CONSTRAINT fk_reservations_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reservations_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_reservations_service
        FOREIGN KEY (service_id) REFERENCES services(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_reservations_time
        CHECK (start_time < end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

