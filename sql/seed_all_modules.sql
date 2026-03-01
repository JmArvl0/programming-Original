USE beyond_the_map;

INSERT INTO users (id, username, password_hash, role, email, is_active)
VALUES
    (1, 'admin', 'admin123', 'admin', 'admin@beyondthemap.local', 1),
    (2, 'ae.maria', 'ae123', 'ae', 'ae.maria@beyondthemap.local', 1),
    (3, 'passport.john', 'staff123', 'staff', 'passport.john@beyondthemap.local', 1),
    (4, 'facilities.anne', 'staff123', 'staff', 'facilities.anne@beyondthemap.local', 1),
    (5, 'finance.mike', 'staff123', 'staff', 'finance.mike@beyondthemap.local', 1)
ON DUPLICATE KEY UPDATE
    email = VALUES(email),
    is_active = VALUES(is_active);

INSERT INTO staff_profiles (id, user_id, full_name, team, phone)
VALUES
    (1, 2, 'Maria Santos', 'account_executive', '09171230001'),
    (2, 3, 'John Reyes', 'passport', '09171230002'),
    (3, 4, 'Anne Cruz', 'facilities', '09171230003'),
    (4, 5, 'Mike Dela Rosa', 'finance', '09171230004')
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    team = VALUES(team),
    phone = VALUES(phone);

INSERT INTO customers (
    id, full_name, email, phone, destination, tier, status, payment_status, admission_status, progress, refund_flag, created_at, last_contacted_at
)
VALUES
    -- NEW TAB (created within 7 days)
    (1, 'Vanessa Radaza', 'vanessa@mail.com', '09181110001', 'Japan', 'gold', 'pending', 'unpaid', 'pending', 18, 0, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 1 DAY),
    (2, 'Erick Taguba', 'erick@mail.com', '09181110002', 'France', 'silver', 'pending', 'partially paid', 'pending', 30, 0, NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 2 DAY),

    -- FOR FOLLOW-UP TAB (documents/status pending; payment should not matter)
    (3, 'Rens Solano', 'rens@mail.com', '09181110003', 'Canada', 'vip', 'pending', 'paid', 'admitted', 42, 0, NOW() - INTERVAL 18 DAY, NOW() - INTERVAL 4 DAY),

    -- ONGOING TAB (documents/status processing; payment should not matter)
    (4, 'Maria Alvares', 'maria@mail.com', '09181110004', 'Australia', 'new', 'processing', 'overdue', 'pending', 58, 0, NOW() - INTERVAL 14 DAY, NOW() - INTERVAL 3 DAY),
    (5, 'Joseph De Guzman', 'joseph@mail.com', '09181110005', 'UK', 'gold', 'processing', 'paid', 'admitted', 76, 0, NOW() - INTERVAL 22 DAY, NOW() - INTERVAL 1 DAY),

    -- FINISHED TAB (must be finished + paid)
    (6, 'Sarah Dela Cruz', 'sarah@mail.com', '09181110006', 'Dubai', 'silver', 'finished', 'paid', 'admitted', 100, 0, NOW() - INTERVAL 45 DAY, NOW() - INTERVAL 2 DAY),

    -- PAYMENT ISSUES TAB (unpaid/overdue focus)
    (7, 'Mark Villotes', 'mark@mail.com', '09181110007', 'Singapore', 'silver', 'processing', 'unpaid', 'pending', 51, 0, NOW() - INTERVAL 11 DAY, NOW() - INTERVAL 5 DAY),
    (8, 'James Cruz', 'james@mail.com', '09181110008', 'Italy', 'new', 'pending', 'overdue', 'pending', 27, 0, NOW() - INTERVAL 9 DAY, NOW() - INTERVAL 3 DAY),

    -- REFUND TAB (refund flag true; status does not matter)
    (9, 'Jennifer Reyes', 'jennifer@mail.com', '09181110009', 'Spain', 'gold', 'cancelled', 'paid', 'pending', 35, 1, NOW() - INTERVAL 28 DAY, NOW() - INTERVAL 6 DAY)
ON DUPLICATE KEY UPDATE
    destination = VALUES(destination),
    tier = VALUES(tier),
    status = VALUES(status),
    payment_status = VALUES(payment_status),
    admission_status = VALUES(admission_status),
    progress = VALUES(progress),
    refund_flag = VALUES(refund_flag),
    last_contacted_at = VALUES(last_contacted_at);

INSERT INTO crm_interactions (customer_id, ae_staff_id, interaction_type, details, next_action_at)
VALUES
    (1, 1, 'follow_up', 'Confirmed travel preferences and hotel category.', NOW() + INTERVAL 2 DAY),
    (2, 1, 'inquiry', 'Customer asked for promo package options.', NOW() + INTERVAL 1 DAY),
    (4, 1, 'clarification', 'Waiting for passport copy and visa photo.', NOW() + INTERVAL 3 DAY),
    (6, 1, 'booking', 'Finalized itinerary and draft invoice sent.', NOW() + INTERVAL 1 DAY);

INSERT INTO passport_applications (
    id, customer_id, passport_number, country, documents_status, application_status, priority, submission_date, remarks
)
VALUES
    (1, 1, 'DAS2301A', 'Japan', 'approved', 'processing', 'medium', CURDATE() - INTERVAL 10 DAY, 'Waiting embassy slot'),
    (2, 2, 'IN1209B', 'France', 'missing', 'action required', 'high', CURDATE() - INTERVAL 7 DAY, 'Missing bank certificate'),
    (3, 3, 'REV8765C', 'Canada', 'approved', 'visa issued', 'low', CURDATE() - INTERVAL 20 DAY, 'Completed'),
    (4, 4, 'EXP5533D', 'Australia', 'submitted', 'under review', 'high', CURDATE() - INTERVAL 5 DAY, 'Under evaluation')
ON DUPLICATE KEY UPDATE
    documents_status = VALUES(documents_status),
    application_status = VALUES(application_status),
    priority = VALUES(priority),
    remarks = VALUES(remarks);

INSERT INTO passport_documents (passport_application_id, document_type, file_path, status, reviewed_by_staff_id, reviewed_at)
VALUES
    (1, 'Passport Bio Page', 'uploads/passport_1_bio.pdf', 'approved', 2, NOW() - INTERVAL 9 DAY),
    (2, 'Bank Certificate', 'uploads/passport_2_bank.pdf', 'missing', 2, NOW() - INTERVAL 6 DAY),
    (3, 'Visa Form', 'uploads/passport_3_form.pdf', 'approved', 2, NOW() - INTERVAL 18 DAY),
    (4, 'Photo ID', 'uploads/passport_4_photo.pdf', 'submitted', NULL, NULL);

INSERT INTO tours (id, tour_name, destination, rate, capacity, departure_time, tour_date, status, duration_days)
VALUES
    (1, 'Kyoto Cultural Walk', 'Kyoto, Japan', 25500.00, 30, '08:30:00', CURDATE() + INTERVAL 5 DAY, 'open', 5),
    (2, 'Paris City Escape', 'Paris, France', 32000.00, 25, '10:00:00', CURDATE() + INTERVAL 9 DAY, 'limited', 6),
    (3, 'Sydney Coastal Adventure', 'Sydney, Australia', 28000.00, 20, '13:30:00', CURDATE() + INTERVAL 12 DAY, 'open', 4),
    (4, 'Dubai Desert Experience', 'Dubai, UAE', 22000.00, 18, '15:00:00', CURDATE() + INTERVAL 7 DAY, 'upcoming', 3)
ON DUPLICATE KEY UPDATE
    rate = VALUES(rate),
    capacity = VALUES(capacity),
    departure_time = VALUES(departure_time),
    tour_date = VALUES(tour_date),
    status = VALUES(status);

INSERT INTO guests (id, customer_id, full_name, email, phone)
VALUES
    (1, 1, 'Vanessa Radaza', 'vanessa@mail.com', '09181110001'),
    (2, 2, 'Erick Taguba', 'erick@mail.com', '09181110002'),
    (3, 3, 'Rens Solano', 'rens@mail.com', '09181110003'),
    (4, 4, 'Maria Alvares', 'maria@mail.com', '09181110004'),
    (5, 6, 'Sarah Dela Cruz', 'sarah@mail.com', '09181110006')
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    email = VALUES(email),
    phone = VALUES(phone);

INSERT INTO bookings (tour_id, guest_id, booking_date, booking_status, seat_number, amount, payment_status)
VALUES
    (1, 1, CURDATE() + INTERVAL 5 DAY, 'confirmed', 'A-01', 25500.00, 'partial'),
    (2, 2, CURDATE() + INTERVAL 9 DAY, 'reserved', 'B-03', 32000.00, 'pending'),
    (3, 3, CURDATE() + INTERVAL 12 DAY, 'confirmed', 'C-02', 28000.00, 'paid'),
    (4, 4, CURDATE() + INTERVAL 7 DAY, 'reserved', 'D-05', 22000.00, 'overdue'),
    (1, 5, CURDATE() + INTERVAL 5 DAY, 'confirmed', 'A-02', 25500.00, 'paid');

INSERT INTO facilities (id, facility_name, facility_type, capacity, location, is_active)
VALUES
    (1, 'Business Lounge A', 'Lounge', 40, 'Terminal 1', 1),
    (2, 'VIP Reception Suite', 'VIP', 12, 'Terminal 3', 1),
    (3, 'Arrival Assistance Desk', 'Assistance', 25, 'Terminal 2', 1),
    (4, 'Porter Group Alpha', 'Porter Service', 20, 'Terminal 1', 1)
ON DUPLICATE KEY UPDATE
    capacity = VALUES(capacity),
    location = VALUES(location),
    is_active = VALUES(is_active);

INSERT INTO facility_reservations (
    id, customer_id, booking_id, facility_id, booking_reference, service_type, reservation_date, priority, status, notes
)
VALUES
    (1, 1, 1, 1, 'BK-2602-1001', 'Lounge', CURDATE() + INTERVAL 5 DAY, 'high', 'approved', 'VIP early check-in support'),
    (2, 2, 2, 3, 'BK-2602-1002', 'Assistance', CURDATE() + INTERVAL 9 DAY, 'normal', 'requested', 'Wheelchair assistance request'),
    (3, 3, 3, 2, 'BK-2602-1003', 'VIP', CURDATE() + INTERVAL 12 DAY, 'normal', 'in progress', 'Security escort included'),
    (4, 4, 4, 4, 'BK-2602-1004', 'Porter Service', CURDATE() + INTERVAL 7 DAY, 'low', 'completed', 'Baggage support completed')
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    priority = VALUES(priority),
    notes = VALUES(notes);

INSERT INTO facility_coordination_status (
    facility_reservation_id, assigned_staff_id, logistics_status, completion_time, remarks
)
VALUES
    (1, 3, 'dispatched', NULL, 'Staff en route to meet client'),
    (2, 3, 'queued', NULL, 'Awaiting assignment'),
    (3, 3, 'arrived', NULL, 'Client has arrived at reception'),
    (4, 3, 'completed', NOW() - INTERVAL 1 DAY, 'Service finished successfully');

INSERT INTO payments (customer_id, booking_id, amount, due_date, paid_at, status, reference_no)
VALUES
    (1, 1, 25500.00, CURDATE() + INTERVAL 3 DAY, NULL, 'partial', 'PAY-0001'),
    (2, 2, 32000.00, CURDATE() + INTERVAL 2 DAY, NULL, 'pending', 'PAY-0002'),
    (3, 3, 28000.00, CURDATE() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY, 'paid', 'PAY-0003'),
    (4, 4, 22000.00, CURDATE() - INTERVAL 2 DAY, NULL, 'overdue', 'PAY-0004'),
    (6, 5, 25500.00, CURDATE() + INTERVAL 4 DAY, NOW(), 'paid', 'PAY-0005');

INSERT INTO payment_reminders (payment_id, reminder_type, reminder_status, sent_at)
SELECT p.id, 'in_app', IF(p.status IN ('pending', 'overdue', 'partial'), 'sent', 'queued'), NOW()
FROM payments p
WHERE p.reference_no IN ('PAY-0001', 'PAY-0002', 'PAY-0004');

INSERT INTO messages (sender_role, sender_id, receiver_role, receiver_id, module_origin, message_text, is_read, created_at)
VALUES
    ('customer', 1, 'ae', 1, 'CRM', 'Hi AE, can you confirm my updated itinerary?', 0, NOW() - INTERVAL 2 HOUR),
    ('ae', 1, 'customer', 1, 'CRM', 'Confirmed. I sent the revised itinerary to your email.', 1, NOW() - INTERVAL 90 MINUTE),
    ('customer', 2, 'ae', 1, 'Passport', 'I submitted my bank certificate just now.', 0, NOW() - INTERVAL 70 MINUTE),
    ('ae', 1, 'staff', 2, 'Passport', 'Please prioritize document review for customer #2.', 0, NOW() - INTERVAL 45 MINUTE),
    ('staff', 3, 'ae', 1, 'Facilities', 'Reservation #1 has been dispatched to the team.', 0, NOW() - INTERVAL 25 MINUTE);

INSERT INTO notifications (type, module_source, related_id, message, is_read, created_at)
VALUES
    ('status_update', 'Passport', 2, 'Passport: Missing requirement flagged for customer #2.', 0, NOW() - INTERVAL 40 MINUTE),
    ('reservation_confirmed', 'Facilities', 1, 'Facilities: Reservation #1 has been approved.', 0, NOW() - INTERVAL 35 MINUTE),
    ('status_update', 'Schedule', 1, 'Schedule: Booking confirmed for Kyoto Cultural Walk.', 0, NOW() - INTERVAL 28 MINUTE),
    ('payment_due', 'Financial', 4, 'Financial: Payment PAY-0004 is overdue.', 0, NOW() - INTERVAL 20 MINUTE),
    ('status_update', 'CRM', 6, 'CRM: Customer #6 profile has been updated.', 1, NOW() - INTERVAL 15 MINUTE);