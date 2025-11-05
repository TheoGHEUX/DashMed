DELETE FROM SUIVRE;

INSERT INTO SUIVRE (med_id, pt_id, date_debut, date_fin)
VALUES
-- Médecin 1 : amirnexi900@gmail.com (patients 1 à 13)
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 1,  '2025-10-01', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 2,  '2025-10-05', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 3,  '2025-10-09', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 4,  '2025-10-12', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 5,  '2025-10-16', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 6,  '2025-10-19', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 7,  '2025-10-23', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 8,  '2025-10-26', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 9,  '2025-10-29', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 10, '2025-11-02', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 11, '2025-11-08', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 12, '2025-11-15', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 13, '2025-11-28', NULL),

-- Médecin 2 : theoxghx@gmail.com (patients 14 à 26)
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 14, '2025-10-02', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 15, '2025-10-07', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 16, '2025-10-10', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 17, '2025-10-15', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 18, '2025-10-20', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 19, '2025-10-25', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 20, '2025-10-30', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 21, '2025-11-03', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 22, '2025-11-08', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 23, '2025-11-13', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 24, '2025-11-18', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 25, '2025-11-24', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 26, '2025-11-29', NULL),

-- Médecin 3 : abali34000@gmail.com (patients 27 à 38)
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 27, '2025-10-03', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 28, '2025-10-08', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 29, '2025-10-11', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 30, '2025-10-17', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 31, '2025-10-21', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 32, '2025-10-27', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 33, '2025-11-01', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 34, '2025-11-06', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 35, '2025-11-12', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 36, '2025-11-17', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 37, '2025-11-22', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 38, '2025-11-27', NULL),

-- Médecin 4 : alexisfabre2006@gmail.com (patients 39 à 50)
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 39, '2025-10-04', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 40, '2025-10-09', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 41, '2025-10-14', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 42, '2025-10-18', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 43, '2025-10-23', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 44, '2025-10-28', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 45, '2025-11-04', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 46, '2025-11-09', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 47, '2025-11-14', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 48, '2025-11-20', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 49, '2025-11-25', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 50, '2025-11-30', NULL),

-- Patients suivis par deux médecins (5 patients)
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 10, '2025-11-05', NULL),
((SELECT med_id FROM medecin WHERE email='abali34000@gmail.com'), 20, '2025-11-07', NULL),
((SELECT med_id FROM medecin WHERE email='alexisfabre2006@gmail.com'), 30, '2025-11-10', NULL),
((SELECT med_id FROM medecin WHERE email='amirnexi900@gmail.com'), 40, '2025-11-12', NULL),
((SELECT med_id FROM medecin WHERE email='theoxghx@gmail.com'), 50, '2025-11-18', NULL);
