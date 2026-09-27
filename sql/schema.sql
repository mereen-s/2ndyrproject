CREATE DATABASE IF NOT EXISTS ccwlcs CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ccwlcs;

CREATE TABLE department (
  department_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  info VARCHAR(255) NULL
);

CREATE TABLE user (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(40) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(80) NOT NULL,
  role ENUM('Receptionist','OPDDoctor','ClinicDoctor','WardDoctor','WardNurse','LabPersonnel','RadiologyPersonnel','Pharmacist','Administrator') NOT NULL,
  department_id INT NULL,
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (department_id) REFERENCES department(department_id)
);

CREATE TABLE patient (
  patient_id VARCHAR(10) PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  nic VARCHAR(15),
  dob DATE,
  gender ENUM('M','F') NOT NULL,
  contact VARCHAR(20),
  address VARCHAR(150),
  registered_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bed (
  bed_id INT AUTO_INCREMENT PRIMARY KEY,
  department_id INT NOT NULL,
  bed_number INT NOT NULL,
  status ENUM('Free','Occupied') NOT NULL DEFAULT 'Free',
  FOREIGN KEY (department_id) REFERENCES department(department_id)
);

CREATE TABLE admission (
  admission_id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id VARCHAR(10) NOT NULL,
  department_id INT NOT NULL,
  bed_id INT NULL,
  status ENUM('Pending','Admitted','Discharged') NOT NULL DEFAULT 'Pending',
  diagnosis VARCHAR(255) NULL,
  admit_date DATETIME NULL,
  discharge_date DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patient(patient_id),
  FOREIGN KEY (department_id) REFERENCES department(department_id),
  FOREIGN KEY (bed_id) REFERENCES bed(bed_id)
);

CREATE TABLE encounter (
  encounter_id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id VARCHAR(10) NOT NULL,
  doctor_id INT NOT NULL,
  type ENUM('OPD','CLINIC','WARD') NOT NULL,
  history_note VARCHAR(255), pain TINYINT NULL,
  temp DECIMAL(4,1) NULL, bp VARCHAR(10) NULL, pulse SMALLINT NULL, exam_note VARCHAR(255),
  investigations_note VARCHAR(255),
  progress ENUM('Improving','Stable','Deteriorating') NULL, diagnosis VARCHAR(255),
  med_action ENUM('Continue','Change','Stop') NULL, plan_note VARCHAR(255),
  pathway ENUM('Treat','Refer','Admit') NULL,
  referral_department_id INT NULL,
  follow_up_date DATE NULL,
  admission_id INT NULL, note_date DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patient(patient_id),
  FOREIGN KEY (doctor_id) REFERENCES user(user_id),
  FOREIGN KEY (admission_id) REFERENCES admission(admission_id)
);

CREATE TABLE medication_admin (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  admission_id INT NOT NULL,
  nurse_id INT NOT NULL,
  drug_dose VARCHAR(120) NOT NULL,
  route VARCHAR(30),
  admin_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admission_id) REFERENCES admission(admission_id),
  FOREIGN KEY (nurse_id) REFERENCES user(user_id)
);

CREATE TABLE test_type (
  test_code VARCHAR(2) PRIMARY KEY,
  test_name VARCHAR(60) NOT NULL,
  reference_range VARCHAR(60) NULL
);

CREATE TABLE lab_request (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id VARCHAR(10) NOT NULL,
  doctor_id INT NOT NULL,
  test_code VARCHAR(2) NOT NULL,
  request_no INT NOT NULL,
  barcode VARCHAR(25) NOT NULL,
  status ENUM('Requested','Collected','Processing','Completed') NOT NULL DEFAULT 'Requested',
  request_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patient(patient_id),
  FOREIGN KEY (doctor_id) REFERENCES user(user_id),
  FOREIGN KEY (test_code) REFERENCES test_type(test_code)
);

CREATE TABLE lab_result (
  result_id INT AUTO_INCREMENT PRIMARY KEY,
  request_id INT NOT NULL UNIQUE,
  specimen VARCHAR(40),
  finding TEXT,
  entered_by INT NULL,
  entry_time DATETIME NULL,
  accept_status ENUM('Pending','Accepted') NOT NULL DEFAULT 'Pending',
  critical_flag TINYINT NOT NULL DEFAULT 0,
  numeric_result DECIMAL(12,2) NULL,
  unit VARCHAR(20) NULL,
  FOREIGN KEY (request_id) REFERENCES lab_request(request_id),
  FOREIGN KEY (entered_by) REFERENCES user(user_id)
);

CREATE TABLE rad_request (
  rad_request_id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id VARCHAR(10) NOT NULL,
  doctor_id INT NOT NULL,
  scan_type ENUM('X-ray','CT') NOT NULL,
  body_part VARCHAR(60),
  request_no INT NOT NULL,
  barcode VARCHAR(25) NOT NULL,
  status ENUM('Requested','Completed') NOT NULL DEFAULT 'Requested',
  request_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patient(patient_id),
  FOREIGN KEY (doctor_id) REFERENCES user(user_id)
);

CREATE TABLE rad_image (
  image_id INT AUTO_INCREMENT PRIMARY KEY,
  rad_request_id INT NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  uploaded_by INT NOT NULL,
  upload_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  critical_flag TINYINT NOT NULL DEFAULT 0,
  FOREIGN KEY (rad_request_id) REFERENCES rad_request(rad_request_id),
  FOREIGN KEY (uploaded_by) REFERENCES user(user_id)
);

CREATE TABLE prescription (
  prescription_id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id VARCHAR(10) NOT NULL,
  doctor_id INT NOT NULL,
  drug VARCHAR(100) NOT NULL, dose VARCHAR(40), frequency VARCHAR(40), duration VARCHAR(40),
  status ENUM('Pending','Dispensed') NOT NULL DEFAULT 'Pending',
  prescribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  dispensed_by INT NULL, dispensed_at DATETIME NULL, quantity VARCHAR(20) NULL,
  FOREIGN KEY (patient_id) REFERENCES patient(patient_id),
  FOREIGN KEY (doctor_id) REFERENCES user(user_id),
  FOREIGN KEY (dispensed_by) REFERENCES user(user_id)
);

CREATE TABLE notification (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  recipient_user_id INT NOT NULL,
  patient_id VARCHAR(10) NULL,
  source VARCHAR(40),
  message VARCHAR(255) NOT NULL,
  status ENUM('Unread','Viewed') NOT NULL DEFAULT 'Unread',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (recipient_user_id) REFERENCES user(user_id),
  FOREIGN KEY (patient_id) REFERENCES patient(patient_id)
);

CREATE TABLE opd_queue (
  queue_id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id VARCHAR(10) NOT NULL,
  status ENUM('Waiting','Seen') NOT NULL DEFAULT 'Waiting',
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patient(patient_id)
);

CREATE TABLE permission (
  role VARCHAR(30) NOT NULL,
  page VARCHAR(40) NOT NULL,
  PRIMARY KEY (role, page)
);

CREATE TABLE audit_log (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(60) NOT NULL,
  entity_ref VARCHAR(80),
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES user(user_id)
);

INSERT INTO department(name) VALUES ('Surgery'),('Medicine'),('Paediatrics');
INSERT INTO bed(department_id, bed_number) SELECT d.department_id, n.n FROM department d
  JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) n;
INSERT INTO test_type(test_code,test_name,reference_range) VALUES
('41','Urine culture (Microbiology)','< 10,000 CFU/mL'),
('42','Blood culture (Microbiology)','No growth'),
('43','Histopathology - biopsy',NULL),
('44','Sputum microscopy','No AFB seen');

INSERT INTO permission(role,page) VALUES
('Administrator','reports'),
('Administrator','report_lab'),('LabPersonnel','report_lab'),
('Administrator','report_ward'),('WardDoctor','report_ward'),('WardNurse','report_ward'),
('Administrator','report_patients'),('Receptionist','report_patients'),
('Receptionist','patient_delete'),
('WardNurse','admission_cancel'),
('LabPersonnel','lab_cancel'),
('Pharmacist','presc_cancel'),
('Receptionist','registration'),
('Receptionist','register_patient'),
('Receptionist','profile'),
('Receptionist','patient_update'),
('Receptionist','opd_assign'),
('Receptionist','api_patient_search'),
('OPDDoctor','api_patient_search'),
('ClinicDoctor','api_patient_search'),
('WardDoctor','api_patient_search'),
('OPDDoctor','history'),
('ClinicDoctor','history'),
('WardDoctor','history'),
('OPDDoctor','orders'),
('ClinicDoctor','orders'),
('WardDoctor','orders'),
('OPDDoctor','prescribe'),
('ClinicDoctor','prescribe'),
('WardDoctor','prescribe'),
('OPDDoctor','request_lab'),
('ClinicDoctor','request_lab'),
('WardDoctor','request_lab'),
('OPDDoctor','request_rad'),
('ClinicDoctor','request_rad'),
('WardDoctor','request_rad'),
('OPDDoctor','opd_form'),
('OPDDoctor','opd_save'),
('ClinicDoctor','clinic'),
('ClinicDoctor','clinic_form'),
('ClinicDoctor','clinic_save'),
('ClinicDoctor','clinic_print'),
('WardDoctor','ward'),
('WardDoctor','ward_note'),
('WardDoctor','ward_note_save'),
('WardDoctor','discharge_preview'),
('WardDoctor','discharge'),
('WardDoctor','discharge_print'),
('WardNurse','nurse'),
('WardNurse','assign_bed'),
('WardNurse','med_admin'),
('WardNurse','ward_info_save'),
('LabPersonnel','lab'),
('LabPersonnel','lab_entry'),
('LabPersonnel','lab_save'),
('LabPersonnel','lab_accept'),
('LabPersonnel','lab_reject'),
('RadiologyPersonnel','radiology'),
('RadiologyPersonnel','rad_upload'),
('RadiologyPersonnel','rad_upload_save'),
('Pharmacist','pharmacy'),
('Pharmacist','dispense'),
('Administrator','admin_users'),
('Administrator','admin_user_create'),
('Administrator','admin_user_update'),
('Administrator','audit'),
('Administrator','admin_perms'),
('Administrator','admin_perms_save');
