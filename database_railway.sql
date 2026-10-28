SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(150) NOT NULL,
 username VARCHAR(80) NOT NULL UNIQUE,
 email VARCHAR(190) NULL UNIQUE,
 department VARCHAR(150) NULL,
 password VARCHAR(255) NOT NULL,
 role ENUM('admin','user') NOT NULL DEFAULT 'user',
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS catalogs (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 type VARCHAR(80) NOT NULL,
 value VARCHAR(190) NOT NULL,
 sort_order INT NOT NULL DEFAULT 0,
 UNIQUE KEY uq_catalog(type,value),
 KEY idx_catalog_type(type,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tasks (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(80) NOT NULL UNIQUE,
 level ENUM('PROCESO','ETAPA') NOT NULL DEFAULT 'PROCESO',
 parent_code VARCHAR(80) NULL,
 area VARCHAR(150) NULL,
 project VARCHAR(255) NULL,
 task VARCHAR(255) NOT NULL,
 responsible VARCHAR(190) NULL,
 priority VARCHAR(80) NULL,
 status VARCHAR(80) NULL,
 start_date DATE NULL,
 end_date DATE NULL,
 dependency TEXT NULL,
 purchase_milestone VARCHAR(150) NULL,
 reference_no VARCHAR(190) NULL,
 amount DECIMAL(15,2) NULL,
 provider VARCHAR(190) NULL,
 next_milestone VARCHAR(190) NULL,
 next_date DATE NULL,
 risk VARCHAR(80) NULL,
 original_start_date DATE NULL,
 original_end_date DATE NULL,
 created_by INT UNSIGNED NULL,
 updated_by INT UNSIGNED NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 KEY idx_task_area(area), KEY idx_task_status(status), KEY idx_task_dates(start_date,end_date),
 CONSTRAINT fk_tasks_creator FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
 CONSTRAINT fk_tasks_updater FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS task_date_history (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 task_id BIGINT UNSIGNED NOT NULL,
 old_start_date DATE NULL,
 new_start_date DATE NULL,
 old_end_date DATE NULL,
 new_end_date DATE NULL,
 reason TEXT NOT NULL,
 changed_by INT UNSIGNED NULL,
 changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_hist_task(task_id),
 CONSTRAINT fk_hist_task FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE,
 CONSTRAINT fk_hist_user FOREIGN KEY(changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NULL,
 action VARCHAR(120) NOT NULL,
 detail TEXT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_audit_user(user_id), KEY idx_audit_date(created_at),
 CONSTRAINT fk_audit_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS alert_log (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 alert_key VARCHAR(255) NOT NULL UNIQUE,
 task_id BIGINT UNSIGNED NULL,
 email VARCHAR(190) NOT NULL,
 alert_type VARCHAR(80) NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_alert_task(task_id),
 CONSTRAINT fk_alert_task FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 token_hash CHAR(64) NOT NULL UNIQUE,
 expires_at DATETIME NOT NULL,
 used_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_reset_user(user_id),
 CONSTRAINT fk_reset_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO catalogs(type,value,sort_order) VALUES
('priority','Alta',10),('priority','Media',20),('priority','Baja',30),
('status','Pendiente',10),('status','En curso',20),('status','Terminada',30),('status','Completada',40),('status','Cancelada',50),
('risk','Alto',10),('risk','Medio',20),('risk','Bajo',30),
('area','Administración',10),('area','Bienes y Servicios',20),('area','Bodega',30),('area','Dirección Regional',40),
('area','Finanzas',50),('area','Frontera',60),('area','Inventario',70),('area','Prevención de Riesgos',80),
('area','RR.HH.',90),('area','TI',100),('area','Otra',110);
