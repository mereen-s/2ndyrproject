-- Run this ONLY if your database was created before this change.
-- Adds the optional numeric result, unit and reference range used on the lab entry screen.
ALTER TABLE test_type  ADD COLUMN reference_range VARCHAR(60) NULL;
ALTER TABLE lab_result ADD COLUMN numeric_result DECIMAL(12,2) NULL;
ALTER TABLE lab_result ADD COLUMN unit VARCHAR(20) NULL;
UPDATE test_type SET reference_range='< 10,000 CFU/mL' WHERE test_code='41';
UPDATE test_type SET reference_range='No growth'       WHERE test_code='42';
UPDATE test_type SET reference_range='No AFB seen'     WHERE test_code='44';

-- Records the client IP on each audit entry. Without this column every audit write fails silently.
ALTER TABLE audit_log ADD COLUMN ip_address VARCHAR(45) NULL;

-- Lets the Administrator manage report access on the Permissions screen.
INSERT IGNORE INTO permission(role,page) VALUES
('Administrator','reports'),
('Administrator','report_lab'),('LabPersonnel','report_lab'),
('Administrator','report_ward'),('WardDoctor','report_ward'),('WardNurse','report_ward'),
('Administrator','report_patients'),('Receptionist','report_patients');
