

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

-- Wstawianie danych testowych

-- Dane testowe dla tabeli users
INSERT INTO users (id, name, surname, email, password, phone, role, active, created_at) VALUES
('10000000-0000-0000-0000-000000000001', 'Jan', 'Kowalski', 'jan.kowalski@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123456789', 'client', 1, NOW()),
('10000000-0000-0000-0000-000000000002', 'Anna', 'Nowak', 'anna.nowak@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '987654321', 'client', 1, NOW()),
('10000000-0000-0000-0000-000000000003', 'Piotr', 'Wiśniewski', 'piotr.wisniewski@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555666777', 'employee', 1, NOW()),
('10000000-0000-0000-0000-000000000004', 'Maria', 'Wójcik', 'maria.wojcik@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '444333222', 'employee', 1, NOW()),
('10000000-0000-0000-0000-000000000005', 'Adam', 'Kowalczyk', 'adam.kowalczyk@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '111222333', 'admin', 1, NOW());

-- Dane testowe dla tabeli service_categories
INSERT INTO service_categories (id, name, description) VALUES
('20000000-0000-0000-0000-000000000001', 'Styling', 'Usługi związane z stylizacją i fryzurami'),
('20000000-0000-0000-0000-000000000002', 'Pieleń', 'Usługi pielęgnacyjne wizualne'),
('20000000-0000-0000-0000-000000000003', 'Farba', 'Usługi związane z farbowaniem włosów');

-- Dane testowe dla tabeli services
INSERT INTO services (id, category_id, name, description, duration, price, active) VALUES
('30000000-0000-0000-0000-000000000001', '20000000-0000-0000-0000-000000000001', 'Fryzura damowa klasyczna', 'Klasyczna fryzura damowa z woskiem lub farbą', 45, 80.00, 1),
('30000000-0000-0000-0000-000000000002', '20000000-0000-0000-0000-000000000001', 'Fryzura mężczyzn', 'Prosta fryzura mężczyzn', 30, 50.00, 1),
('30000000-0000-0000-0000-000000000003', '20000000-0000-0000-0000-000000000001', 'Fryzura z wędką', 'Zaawansowana fryzura z wędką', 60, 120.00, 1),
('30000000-0000-0000-0000-000000000004', '20000000-0000-0000-0000-000000000002', 'Maska nawilżająca', 'Intensywna masa nawilżająca', 30, 45.00, 1),
('30000000-0000-0000-0000-000000000005', '20000000-0000-0000-0000-000000000003', 'Farba na włosy', 'Farba profesjonalna na włosy', 90, 150.00, 1);

-- Dane testowe dla tabeli employees
INSERT INTO employees (id, user_id, description, active) VALUES
('40000000-0000-0000-0000-000000000001', '10000000-0000-0000-0000-000000000003', 'Doświadczony fryzjer specializee w obszarze stylizacji', 1),
('40000000-0000-0000-0000-000000000002', '10000000-0000-0000-0000-000000000004', 'Specjalistka od pielęgnacji wizualnej', 1);

-- Dane testowe dla tabeli employee_services
INSERT INTO employee_services (employee_id, service_id) VALUES
('40000000-0000-0000-0000-000000000001', '30000000-0000-0000-0000-000000000001'),
('40000000-0000-0000-0000-000000000001', '30000000-0000-0000-0000-000000000002'),
('40000000-0000-0000-0000-000000000001', '30000000-0000-0000-0000-000000000003'),
('40000000-0000-0000-0000-000000000002', '30000000-0000-0000-0000-000000000004'),
('40000000-0000-0000-0000-000000000002', '30000000-0000-0000-0000-000000000005');

-- Dane testowe dla tabeli employee_availability
INSERT INTO employee_availability (id, employee_id, day_of_week, start_time, end_time) VALUES
('50000000-0000-0000-0000-000000000001', '40000000-0000-0000-0000-000000000001', 1, '09:00:00', '17:00:00'),
('50000000-0000-0000-0000-000000000002', '40000000-0000-0000-0000-000000000001', 2, '09:00:00', '17:00:00'),
('50000000-0000-0000-0000-000000000003', '40000000-0000-0000-0000-000000000001', 3, '09:00:00', '17:00:00'),
('50000000-0000-0000-0000-000000000004', '40000000-0000-0000-0000-000000000002', 1, '10:00:00', '18:00:00'),
('50000000-0000-0000-0000-000000000005', '40000000-0000-0000-0000-000000000002', 2, '10:00:00', '18:00:00');

-- Dane testowe dla tabeli reservations
INSERT INTO reservations (id, user_id, employee_id, service_id, reservation_date, start_time, end_time, status, comment, created_at) VALUES
('60000000-0000-0000-0000-000000000001', '10000000-0000-0000-0000-000000000001', '40000000-0000-0000-0000-000000000001', '30000000-0000-0000-0000-000000000001', '2026-10-15', '10:00:00', '10:45:00', 'confirmed', 'Proszę o wygodny termin', NOW()),
('60000000-0000-0000-0000-000000000002', '10000000-0000-0000-0000-000000000002', '40000000-0000-0000-0000-000000000001', '30000000-0000-0000-0000-000000000002', '2026-10-16', '14:00:00', '14:30:00', 'pending', 'Przygotuj lekki stylizator', NOW()),
('60000000-0000-0000-0000-000000000003', '10000000-0000-0000-0000-000000000001', '40000000-0000-0000-0000-000000000002', '30000000-0000-0000-0000-000000000004', '2026-10-17', '11:00:00', '11:30:00', 'completed', 'Dziękujemy za usługę', NOW()),
('60000000-0000-0000-0000-000000000004', '10000000-0000-0000-0000-000000000002', '40000000-0000-0000-0000-000000000002', '30000000-0000-0000-0000-000000000005', '2026-10-18', '15:00:00', '16:30:00', 'confirmed', 'Zaplanować farbowanie', NOW()),
('60000000-0000-0000-0000-000000000005', '10000000-0000-0000-0000-000000000001', '40000000-0000-0000-0000-000000000001', '30000000-0000-0000-0000-000000000003', '2026-10-19', '13:00:00', '14:00:00', 'pending', NULL, NOW());

SET FOREIGN_KEY_CHECKS = 1;

