<?php // Run ONCE at http://localhost/ccwlcs/install.php then DELETE this file.
require 'config/config.php'; require 'config/database.php';
$db = Db::get();
$h = password_hash('pass123', PASSWORD_DEFAULT);
$users = [
 ['admin','System Administrator','Administrator',null],
 ['reception1','R. Fernando','Receptionist',null],
 ['opd1','Dr. N. Silva','OPDDoctor',null],
 ['clinic_sur','Dr. A. Jayasinghe (Consultant)','ClinicDoctor',1],
 ['clinic_med','Dr. K. Perera (Consultant)','ClinicDoctor',2],
 ['clinic_ped','Dr. S. Herath (Consultant)','ClinicDoctor',3],
 ['ward_sur','Dr. T. Bandara (IMO)','WardDoctor',1],
 ['ward_med','Dr. M. Dias (IMO)','WardDoctor',2],
 ['ward_ped','Dr. P. Weerasinghe (IMO)','WardDoctor',3],
 ['nurse_sur','Nurse C. Kumari','WardNurse',1],
 ['nurse_med','Nurse D. Ranasinghe','WardNurse',2],
 ['nurse_ped','Nurse H. Peiris','WardNurse',3],
 ['lab1','Lab Tech U. Gunawardena','LabPersonnel',null],
 ['rad1','Radiographer L. Senanayake','RadiologyPersonnel',null],
 ['pharm1','Pharmacist G. Wickrama','Pharmacist',null],
];
$st = $db->prepare("INSERT IGNORE INTO user(username,password_hash,full_name,role,department_id) VALUES (?,?,?,?,?)");
foreach ($users as $u) $st->execute([$u[0],$h,$u[1],$u[2],$u[3]]);
$db->prepare("INSERT IGNORE INTO patient(patient_id,name,nic,dob,gender,contact,address) VALUES ('P00001','A. Perera','902345678V','1990-03-12','M','0771234567','23/4, Temple Road, Nugegoda')")->execute();
echo 'Installed. Accounts created (password: pass123). <b>Delete install.php now.</b> <a href="index.php">Go to login</a>';
