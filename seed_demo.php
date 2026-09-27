<?php

require 'config/config.php';
require 'config/database.php';
$db = Db::get();

// Idempotency guard — do not re-seed
if ($db->query("SELECT COUNT(*) c FROM patient WHERE patient_id='P00002'")->fetch()['c'] > 0) {
    die('<b>Demo data already seeded.</b> Delete seed_demo.php. <a href="index.php">Log in</a>');
}

// helper closures
$uid = fn($u) => "(SELECT user_id FROM user WHERE username='$u')";
$bid = fn($d,$n) => "(SELECT bed_id FROM bed WHERE department_id=$d AND bed_number='$n')";

// 1. Patients
$db->exec("
INSERT INTO patient(patient_id,name,nic,dob,gender,contact,address,registered_date) VALUES
('P00002','K. Fernando',        '885623410V',   '1988-06-02','M','0713456789','45 Galle Road, Dehiwala',          NOW() - INTERVAL 14 DAY),
('P00003','S. Jayawardena',     '926784521V',   '1992-11-19','F','0779812345','112/B Kandy Road, Kadawatha',       NOW() - INTERVAL 10 DAY),
('P00004','M. Silva',           '790234567V',   '1979-01-27','M','0765554321','8 Lake Drive, Rajagiriya',          NOW() - INTERVAL  8 DAY),
('P00005','T. Wickramasinghe',  '200156702341', '2001-08-14','F','0723456780','301 Main Street, Negombo',          NOW() - INTERVAL  5 DAY),
('P00006','R. Perera',          '680912345V',   '1968-04-30','M','0741237890','67 Hill Street, Kurunegala',        NOW() - INTERVAL  4 DAY),
('P00007','N. Gunasekara',      '955671234V',   '1995-09-08','F','0709871234','19 Flower Lane, Moratuwa',          NOW() - INTERVAL  3 DAY),
('P00008','A. Bandara',         '740317892V',   '1974-03-17','M','0756780001','2/A Station Road, Panadura',        NOW() - INTERVAL  3 DAY),
('P00009','P. Rajapaksa',       '010245601234', '2001-02-04','F','0718901234','88 Temple Road, Matara',            NOW() - INTERVAL  2 DAY),
('P00010','D. Hettige',         '600820456V',   '1960-08-20','M','0761234560','15 Park Avenue, Gampaha',           NOW() - INTERVAL  2 DAY),
('P00011','C. Marasinghe',      '870530789V',   '1987-05-30','F','0773456123','77 Main Street, Kalutara',          NOW() - INTERVAL  1 DAY),
('P00012','W. Dissanayake',     '520110234V',   '1952-01-10','M','0751230987','33 Temple Lane, Galle',             NOW() - INTERVAL  1 DAY),
('P00013','I. Seneviratne',     '991223012341', '1999-12-23','F','0714561234','7 Garden Road, Colombo 07',         NOW());
");

// 2. Admissions
$db->exec("
INSERT INTO admission(patient_id,department_id,bed_id,status,diagnosis,admit_date,created_at) VALUES
('P00002',1,{$bid(1,'3')},'Admitted','Acute appendicitis — post appendectomy day 4',  NOW() - INTERVAL 4 DAY, NOW() - INTERVAL 4 DAY),
('P00005',1,{$bid(1,'5')},'Admitted','Community-acquired pneumonia',                  NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY),
('P00010',1,NULL,          'Pending', 'Suspected small-bowel obstruction',             NULL,                   NOW() - INTERVAL 1 DAY),
('P00012',2,{$bid(2,'1')},'Admitted','Hypertensive urgency — BP 210/120 on admission',NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 3 DAY);

UPDATE bed SET status='Occupied' WHERE bed_id={$bid(1,'3')};
UPDATE bed SET status='Occupied' WHERE bed_id={$bid(1,'5')};
UPDATE bed SET status='Occupied' WHERE bed_id={$bid(2,'1')};
");

// 3. OPD encounters
$db->exec("
INSERT INTO encounter(patient_id,doctor_id,type,history_note,pain,temp,bp,pulse,
  exam_note,investigations_note,progress,diagnosis,med_action,plan_note,pathway,referral_department_id,created_at)
VALUES
('P00002',{$uid('opd1')},'OPD','Severe right lower abdominal pain for 12 hours',8,38.2,'130/85',96,
 'Rebound tenderness RIF, guarding positive','FBC + CRP + blood culture ordered',NULL,'Acute appendicitis',NULL,
 'Urgent surgical review — admit for appendectomy','Admit',1, NOW() - INTERVAL 4 DAY),

('P00003',{$uid('opd1')},'OPD','Recurring epigastric burning after meals, 4 weeks',4,36.9,'120/80',78,
 'Epigastric tenderness, no guarding','Urine culture, H. pylori serology ordered',NULL,'Chronic gastritis',NULL,
 'Start PPI, refer to Medicine clinic for follow-up','Refer',2, NOW() - INTERVAL 10 DAY),

('P00004',{$uid('opd1')},'OPD','Productive cough for one week, green sputum',2,37.4,'125/82',84,
 'Coarse crackles left base, no consolidation','Sputum microscopy ordered, CXR requested',
 'Stable','Acute bronchitis','Continue','Amoxicillin 500 mg × 5 days, review if worse','Treat',NULL, NOW() - INTERVAL 8 DAY),

('P00005',{$uid('opd1')},'OPD','High fever, shortness of breath, 3 days',7,39.1,'118/76',108,
 'Reduced breath sounds right base, dullness to percussion','CXR urgent, FBC, blood culture × 2',
 NULL,'Community-acquired pneumonia','Continue','IV antibiotics, admit medical ward','Admit',1, NOW() - INTERVAL 2 DAY),

('P00006',{$uid('opd1')},'OPD','Chest tightness on exertion, 2 weeks',5,36.8,'148/94',88,
 'No murmur, mild peripheral oedema','ECG, FBC, renal function, lipids ordered',
 NULL,'Possible ischaemic heart disease','Continue','Refer to Cardiology clinic','Refer',2, NOW() - INTERVAL 4 DAY),

('P00007',{$uid('opd1')},'OPD','Irregular periods, weight gain, fatigue × 3 months',1,36.7,'116/78',72,
 'No thyroid enlargement, BMI 28','Thyroid function tests, serum BHCG ordered',
 NULL,'Suspected hypothyroidism — await results','Continue','Thyroid function panel ordered, review in 1 week','Treat',NULL, NOW() - INTERVAL 3 DAY),

('P00008',{$uid('opd1')},'OPD','Known diabetic — polyuria, polydipsia increased × 2 weeks',3,36.9,'132/88',80,
 'Mild dehydration, no ketotic breath','Random blood glucose, HbA1c, urine dipstick',
 NULL,'Poor glycaemic control — type 2 DM','Continue','Metformin dose up-titration, dietary review','Treat',NULL, NOW() - INTERVAL 3 DAY),

('P00013',{$uid('opd1')},'OPD','Headache, nausea, neck stiffness — sudden onset today',9,38.5,'110/72',114,
 'Kernig sign positive, photophobia','LP urgent, blood culture × 2, FBC, CRP',
 NULL,'Suspected bacterial meningitis','Continue','Emergency admission — neurology review','Admit',1, NOW() - INTERVAL 30 MINUTE);
");

// 4. Clinic follow-up encounters
$db->exec("
INSERT INTO encounter(patient_id,doctor_id,type,history_note,pain,temp,bp,pulse,
  exam_note,investigations_note,progress,diagnosis,med_action,plan_note,follow_up_date,created_at)
VALUES
('P00003',{$uid('clinic_med')},'CLINIC','Symptoms improving on PPI, heartburn reduced',2,36.8,'118/78',74,
 'Soft abdomen, mild epigastric tenderness only','Urine culture pending','Improving','Chronic gastritis — responding to PPI',
 'Continue','Continue Omeprazole 20 mg, H. pylori result pending — treat if positive',DATE_ADD(CURDATE(), INTERVAL 14 DAY), NOW() - INTERVAL 5 DAY),

('P00006',{$uid('clinic_med')},'CLINIC','Chest tightness improved but exertional dyspnoea persists',4,37.0,'140/90',84,
 'Mild bilateral ankle oedema, clear chest','Echocardiogram pending, lipids received — LDL 4.8 mmol/L',
 'Improving','Suspected stable angina — IHD risk high','Continue',
 'Start aspirin 75 mg, atorvastatin 40 mg, refer for stress ECG',DATE_ADD(CURDATE(), INTERVAL 21 DAY), NOW() - INTERVAL 1 DAY);
");

// 5. Ward daily notes
$db->exec("
INSERT INTO encounter(patient_id,doctor_id,type,history_note,pain,temp,bp,pulse,
  exam_note,investigations_note,progress,diagnosis,med_action,plan_note,admission_id,note_date,created_at)
VALUES
('P00002',{$uid('ward_sur')},'WARD','Moderate pain, not passed flatus yet',     5,38.1,'128/84',92,'Wound clean, mild erythema', 'Blood culture pending',  'Stable',   'Day 1 post-appendectomy','Continue','IV Cefuroxime 750 mg tds, NBM until bowels open', 1, CURDATE()-INTERVAL 3 DAY, NOW()-INTERVAL 3 DAY),
('P00002',{$uid('ward_sur')},'WARD','Slept well, passed flatus, tolerating sips',3,37.5,'124/80',84,'Wound clean and dry',        'Blood culture: E. coli', 'Improving','Day 2 post-appendectomy','Continue','Switch to oral Amoxicillin, start clear fluids',  1, CURDATE()-INTERVAL 2 DAY, NOW()-INTERVAL 2 DAY),
('P00002',{$uid('ward_sur')},'WARD','No pain at rest, walking in corridor',     1,36.9,'120/78',76,'Wound healing well',          'FBC normal today',        'Improving','Day 3 post-appendectomy','Continue','Soft diet, plan discharge tomorrow',              1, CURDATE()-INTERVAL 1 DAY, NOW()-INTERVAL 1 DAY),

('P00005',{$uid('ward_sur')},'WARD','Fever reduced, still dyspnoeic on exertion',5,38.0,'116/74',100,'Reduced right base crackles', 'CXR: right lower lobe infiltrate', 'Stable',  'CAP day 1', 'Continue','IV Co-amoxiclav 1.2 g tds, O2 via mask', 2, CURDATE()-INTERVAL 1 DAY, NOW()-INTERVAL 1 DAY),
('P00005',{$uid('ward_sur')},'WARD','Better, saturations 97% on room air',       3,37.4,'118/76',90,'Improved air entry, minor crackles','Blood culture pending', 'Improving','CAP day 2', 'Continue','Step down to oral antibiotics, plan discharge in 24 h', 2, CURDATE(), NOW()),

('P00012',{$uid('ward_sur')},'WARD','BP settling, headache reduced',3,37.0,'170/102',84,'No papilloedema', 'Renal function normal, ECG — LVH', 'Stable',  'Hypertensive urgency day 2', 'Continue','IV labetalol weaning, monitor hourly BP', 4, CURDATE()-INTERVAL 2 DAY, NOW()-INTERVAL 2 DAY),
('P00012',{$uid('ward_sur')},'WARD','No symptoms, BP 148/92 on oral therapy', 0,36.8,'148/92', 78,'Comfortable at rest',  'Echo: concentric LVH',              'Improving','Hypertensive urgency day 3', 'Continue','Switch to oral Amlodipine 10 mg, discharge plan for tomorrow', 4, CURDATE()-INTERVAL 1 DAY, NOW()-INTERVAL 1 DAY);
");

// 6. Medication administration
$db->exec("
INSERT INTO medication_admin(admission_id,nurse_id,drug_dose,route,admin_time) VALUES
(1,{$uid('nurse_sur')},'Cefuroxime 750 mg',   'IV',   NOW()-INTERVAL 3 DAY),
(1,{$uid('nurse_sur')},'Paracetamol 1 g',     'oral', NOW()-INTERVAL 3 DAY),
(1,{$uid('nurse_sur')},'Cefuroxime 750 mg',   'IV',   NOW()-INTERVAL 2 DAY),
(1,{$uid('nurse_sur')},'Amoxicillin 500 mg',  'oral', NOW()-INTERVAL 1 DAY),
(2,{$uid('nurse_sur')},'Co-amoxiclav 1.2 g',  'IV',   NOW()-INTERVAL 1 DAY),
(2,{$uid('nurse_sur')},'Paracetamol 1 g',     'IV',   NOW()-INTERVAL 1 DAY),
(4,{$uid('nurse_sur')},'Labetalol 200 mg',    'IV',   NOW()-INTERVAL 2 DAY),
(4,{$uid('nurse_sur')},'Amlodipine 10 mg',    'oral', NOW()-INTERVAL 1 DAY);
");

// 7. Lab requests and results
$db->exec("
INSERT INTO lab_request(patient_id,doctor_id,test_code,request_no,barcode,status,request_datetime) VALUES
('P00002',{$uid('ward_sur')}, '42',1,'P00002-42-001','Completed',  NOW()-INTERVAL 3 DAY),
('P00003',{$uid('clinic_med')},'41',1,'P00003-41-001','Completed',  NOW()-INTERVAL 9 DAY),
('P00004',{$uid('opd1')},      '44',1,'P00004-44-001','Completed',  NOW()-INTERVAL 7 DAY),
('P00005',{$uid('ward_sur')}, '42',1,'P00005-42-001','Processing', NOW()-INTERVAL 1 DAY),
('P00006',{$uid('opd1')},      '41',1,'P00006-41-001','Requested',  NOW()-INTERVAL 4 DAY),
('P00008',{$uid('opd1')},      '44',1,'P00008-44-001','Requested',  NOW()-INTERVAL 3 DAY),
('P00013',{$uid('opd1')},      '42',1,'P00013-42-001','Requested',  NOW()-INTERVAL 30 MINUTE);

INSERT INTO lab_result(request_id,specimen,finding,entered_by,entry_time,accept_status,critical_flag) VALUES
(1,'Blood',  'Growth of E. coli after 48 hours — multi-drug resistant profile, urgent clinical review advised',{$uid('lab1')},NOW()-INTERVAL 2 DAY,'Accepted',1),
(2,'Urine',  'No growth after 48 hours — effective treatment confirmed',{$uid('lab1')},NOW()-INTERVAL 7 DAY,'Accepted',0),
(3,'Sputum', 'Heavy growth of Streptococcus pneumoniae — sensitive to Amoxicillin',{$uid('lab1')},NOW()-INTERVAL 6 DAY,'Accepted',0),
(4,'Blood',  'Specimen haemolysed — result invalid',{$uid('lab1')},NOW()-INTERVAL 20 HOUR,'Pending',0);
");

// 8. Radiology requests and images
$db->exec("
INSERT INTO rad_request(patient_id,doctor_id,scan_type,body_part,request_no,barcode,status,request_datetime) VALUES
('P00002',{$uid('ward_sur')}, 'X-ray','chest',      1,'P00002-51-001','Completed', NOW()-INTERVAL 4 DAY),
('P00005',{$uid('opd1')},     'X-ray','chest',      1,'P00005-51-001','Completed', NOW()-INTERVAL 2 DAY),
('P00006',{$uid('opd1')},     'CT',   'coronary',   1,'P00006-52-001', 'Requested', NOW()-INTERVAL 4 DAY),
('P00013',{$uid('opd1')},     'CT',   'head',       1,'P00013-52-001', 'Requested', NOW()-INTERVAL 30 MINUTE);

INSERT INTO rad_image(rad_request_id,file_path,uploaded_by,upload_time,critical_flag) VALUES
(1,'uploads/radiology/P00002-51-001.png',{$uid('rad1')},NOW()-INTERVAL 3 DAY, 0),
(2,'uploads/radiology/P00005-51-001.png',{$uid('rad1')},NOW()-INTERVAL 1 DAY, 0);
");

// 9. Prescriptions
$db->exec("
INSERT INTO prescription(patient_id,doctor_id,drug,dose,frequency,duration,status,prescribed_at,dispensed_by,dispensed_at,quantity) VALUES
('P00004',{$uid('opd1')},      'Amoxicillin',  '500 mg','3x daily',  '5 days',  'Dispensed', NOW()-INTERVAL 7 DAY,{$uid('pharm1')},NOW()-INTERVAL 7 DAY,'15'),
('P00003',{$uid('clinic_med')},'Omeprazole',   '20 mg', 'daily',     '28 days', 'Dispensed', NOW()-INTERVAL 5 DAY,{$uid('pharm1')},NOW()-INTERVAL 5 DAY,'28'),
('P00006',{$uid('opd1')},      'Aspirin',      '75 mg', 'daily',     '90 days', 'Pending',   NOW()-INTERVAL 1 DAY, NULL, NULL, NULL),
('P00006',{$uid('clinic_med')},'Atorvastatin', '40 mg', 'at night',  '90 days', 'Pending',   NOW()-INTERVAL 1 DAY, NULL, NULL, NULL),
('P00008',{$uid('opd1')},      'Metformin',    '500 mg','2x daily',  '30 days', 'Pending',   NOW()-INTERVAL 3 DAY, NULL, NULL, NULL),
('P00007',{$uid('opd1')},      'Levothyroxine','50 mcg','once daily','30 days', 'Pending',   NOW()-INTERVAL 2 DAY, NULL, NULL, NULL);
");

// 10. Notifications
$db->exec("
INSERT INTO notification(recipient_user_id,patient_id,source,message,status,created_at) VALUES
({$uid('ward_sur')}, 'P00002','Lab P00002-42-001','CRITICAL lab result: E. coli MDRO for K. Fernando (Blood culture)',          'Unread',  NOW()-INTERVAL 2 DAY),
({$uid('opd1')},     'P00003','Lab P00003-41-001','Lab result ready: S. Jayawardena (Urine culture) — no growth',               'Viewed',  NOW()-INTERVAL 7 DAY),
({$uid('opd1')},     'P00004','Lab P00004-44-001','Lab result ready: M. Silva (Sputum microscopy) — S. pneumoniae',             'Viewed',  NOW()-INTERVAL 6 DAY),
({$uid('clinic_med')},'P00006','Clinic follow-up', 'Follow-up due for R. Perera in 3 weeks — cardiac review',                   'Unread',  NOW()-INTERVAL 1 DAY);
");

// 11. OPD queue
$db->exec("
INSERT INTO opd_queue(patient_id,status,assigned_at) VALUES
('P00007','Waiting', NOW()-INTERVAL 45 MINUTE),
('P00008','Waiting', NOW()-INTERVAL 30 MINUTE),
('P00011','Waiting', NOW()-INTERVAL 20 MINUTE),
('P00013','Waiting', NOW()-INTERVAL 15 MINUTE);
");

// 12. Department bulletin
$db->exec("
UPDATE department SET info='Ward round 08:00. Visiting hours 12:00–13:00 and 17:00–18:00.' WHERE department_id=1;
UPDATE department SET info='Clinic sessions: Mon / Wed / Fri 09:00–12:00.' WHERE department_id=2;
");

// 13. Audit log bootstrap
$db->exec("
INSERT INTO audit_log(user_id,action,entity_ref,created_at) VALUES
({$uid('reception1')},'PATIENT_REGISTER','P00002',  NOW()-INTERVAL 14 DAY),
({$uid('reception1')},'PATIENT_REGISTER','P00003',  NOW()-INTERVAL 10 DAY),
({$uid('reception1')},'PATIENT_REGISTER','P00004',  NOW()-INTERVAL  8 DAY),
({$uid('reception1')},'PATIENT_REGISTER','P00005',  NOW()-INTERVAL  5 DAY),
({$uid('reception1')},'PATIENT_REGISTER','P00006',  NOW()-INTERVAL  4 DAY),
({$uid('reception1')},'PATIENT_REGISTER','P00007',  NOW()-INTERVAL  3 DAY),
({$uid('reception1')},'PATIENT_REGISTER','P00008',  NOW()-INTERVAL  3 DAY),
({$uid('reception1')},'PATIENT_REGISTER','P00009',  NOW()-INTERVAL  2 DAY),
({$uid('reception1')},'PATIENT_REGISTER','P00010',  NOW()-INTERVAL  2 DAY),
({$uid('reception1')},'PATIENT_REGISTER','P00011',  NOW()-INTERVAL  1 DAY),
({$uid('reception1')},'PATIENT_REGISTER','P00012',  NOW()-INTERVAL  1 DAY),
({$uid('reception1')},'PATIENT_REGISTER','P00013',  NOW()),
({$uid('opd1')},      'ENCOUNTER_OPD',  'patient:P00002', NOW()-INTERVAL 4 DAY),
({$uid('opd1')},      'ENCOUNTER_OPD',  'patient:P00003', NOW()-INTERVAL 10 DAY),
({$uid('opd1')},      'ENCOUNTER_OPD',  'patient:P00004', NOW()-INTERVAL 8 DAY),
({$uid('opd1')},      'ENCOUNTER_OPD',  'patient:P00005', NOW()-INTERVAL 2 DAY),
({$uid('opd1')},      'ENCOUNTER_OPD',  'patient:P00006', NOW()-INTERVAL 4 DAY),
({$uid('nurse_sur')}, 'BED_ASSIGN',     'admission:1 bed:3', NOW()-INTERVAL 4 DAY),
({$uid('nurse_sur')}, 'BED_ASSIGN',     'admission:2 bed:5', NOW()-INTERVAL 2 DAY),
({$uid('nurse_sur')}, 'BED_ASSIGN',     'admission:4 bed:1', NOW()-INTERVAL 3 DAY),
({$uid('lab1')},      'LAB_RESULT_ACCEPT','labreq:1', NOW()-INTERVAL 2 DAY),
({$uid('lab1')},      'LAB_RESULT_ACCEPT','labreq:2', NOW()-INTERVAL 7 DAY),
({$uid('lab1')},      'LAB_RESULT_ACCEPT','labreq:3', NOW()-INTERVAL 6 DAY),
(NULL,                'ALERT_SENT',     'to ward_sur - P00002-42-001', NOW()-INTERVAL 2 DAY),
({$uid('pharm1')},    'DISPENSE',       'prescription:1',  NOW()-INTERVAL 7 DAY),
({$uid('pharm1')},    'DISPENSE',       'prescription:2',  NOW()-INTERVAL 5 DAY),
({$uid('rad1')},      'RAD_UPLOAD',     'rad_request:1',   NOW()-INTERVAL 3 DAY),
({$uid('rad1')},      'RAD_UPLOAD',     'rad_request:2',   NOW()-INTERVAL 1 DAY);
");

echo '<h2>Demo data seeded successfully!</h2>
<ul>
  <li>12 patients registered (P00002 – P00013)</li>
  <li>4 admissions (3 with beds, 1 pending)</li>
  <li>8 OPD encounters + 2 clinic follow-ups</li>
  <li>7 ward daily notes across 3 active admissions</li>
  <li>7 lab requests (3 completed, 1 processing, 3 pending) — 1 critical result with alert</li>
  <li>4 radiology requests (2 with uploaded images)</li>
  <li>6 prescriptions (2 dispensed, 4 pending)</li>
  <li>8 medication administration records</li>
  <li>4 notifications (1 critical alert, 3 informational)</li>
  <li>4 OPD queue entries waiting for the OPD doctor</li>
</ul>
<p><b>&#9888; Delete seed_demo.php now!</b> <a href="index.php">Log in</a></p>';
