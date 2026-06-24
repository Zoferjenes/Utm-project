USE fixit_arcade;

INSERT INTO users (name, email, password_hash, role, phone) VALUES
('Arcade Admin', 'admin@fixit.test', '$2y$10$n9WILxvYcBGDvb8OmWYnv.I.3noRzMPc9mjvd5IfR.uE1BCyikT..', 'admin', '010-0000000'),
('Fiyandha Customer', 'customer@fixit.test', '$2y$10$n9WILxvYcBGDvb8OmWYnv.I.3noRzMPc9mjvd5IfR.uE1BCyikT..', 'customer', '011-1111111'),
('Ali Plumber', 'provider@fixit.test', '$2y$10$n9WILxvYcBGDvb8OmWYnv.I.3noRzMPc9mjvd5IfR.uE1BCyikT..', 'provider', '012-2222222'),
('Sara Cleaner', 'sara@fixit.test', '$2y$10$n9WILxvYcBGDvb8OmWYnv.I.3noRzMPc9mjvd5IfR.uE1BCyikT..', 'provider', '013-3333333'),
('Omar Electrician', 'omar@fixit.test', '$2y$10$n9WILxvYcBGDvb8OmWYnv.I.3noRzMPc9mjvd5IfR.uE1BCyikT..', 'provider', '014-4444444'),
('John Doe Plumbing Ltd', 'john@fixit.test', '$2y$10$n9WILxvYcBGDvb8OmWYnv.I.3noRzMPc9mjvd5IfR.uE1BCyikT..', 'provider', '015-5555555'),
('Sarah Quick Sparks', 'sarah.spark@fixit.test', '$2y$10$n9WILxvYcBGDvb8OmWYnv.I.3noRzMPc9mjvd5IfR.uE1BCyikT..', 'provider', '016-6666666');

INSERT INTO service_categories (name, description, icon) VALUES
('Plumbing & Repair', 'Leak repairs, toilet repair, pipe installation, water heater support.', 'pipe'),
('Electrical Systems', 'Fan installation, wiring checks, socket repair, light installation.', 'bolt'),
('Home Cleaning', 'Deep cleaning, move-out cleaning, room sanitizing.', 'sparkle'),
('Gardening', 'Lawn care, plant trimming, simple landscaping.', 'leaf'),
('AC Service', 'AC cleaning, gas top-up, filter service, cooling diagnostics.', 'snowflake'),
('Carpentry Work', 'Door repair, shelf installation, furniture fixes, and small woodwork jobs.', 'hammer');

INSERT INTO provider_profiles
  (user_id, bio, location, latitude, longitude, service_radius_km, base_rate, photo_url, is_verified, kyc_doc_url)
VALUES
(3, 'Experienced plumber for student apartments and small household repairs.', 'Taman Universiti, Skudai', 1.5339000, 103.6299000, 8.00, 50.00, '/provider-ali.svg', 1, 'mock-kyc/ali-plumber.pdf'),
(4, 'Cleaner specializing in weekly room cleaning and move-out deep cleaning.', 'Kangkar Pulai, Johor', 1.5448000, 103.5847000, 12.00, 40.00, '/provider-sara.svg', 1, 'mock-kyc/sara-cleaner.pdf'),
(5, 'Electrical technician for fans, sockets, lights, and small rewiring work.', 'Mutiara Rini, Johor', 1.5155000, 103.6395000, 10.00, 60.00, '/provider-omar.svg', 0, 'mock-kyc/omar-electrician.pdf'),
(6, 'Pending provider from Rejve admin mock, focused on plumbing and repair requests.', 'Johor Bahru City Centre', 1.4629000, 103.7637000, 15.00, 55.00, '/provider-ali.svg', 0, 'mock-kyc/john-doe-plumbing.pdf'),
(7, 'Pending provider from Rejve admin mock, focused on quick electrical service calls.', 'Skudai, Johor', 1.5372000, 103.6603000, 10.00, 65.00, '/provider-omar.svg', 0, 'mock-kyc/sarah-quick-sparks.pdf');

INSERT INTO provider_categories (provider_id, category_id) VALUES
(1, 1),
(1, 5),
(2, 3),
(2, 4),
(3, 2),
(4, 1),
(5, 2);

INSERT INTO jobs (customer_id, provider_id, category_id, status, scheduled_at, address, description, total, final_cost, final_cost_confirmed) VALUES
(2, 1, 1, 'accepted', '2026-06-24 10:00:00', 'Block A, Student Apartment, Skudai', 'Kitchen sink pipe is leaking under the cabinet.', 50.00, NULL, 0),
(2, 2, 3, 'completed', '2026-06-22 14:00:00', 'Taman Universiti, Skudai', 'Move-out deep cleaning for one small room.', 80.00, 85.00, 1);

INSERT INTO job_status_logs (job_id, status, changed_by) VALUES
(1, 'requested', 2),
(1, 'accepted', 3),
(2, 'requested', 2),
(2, 'accepted', 4),
(2, 'completed', 4);

INSERT INTO reviews (job_id, rating, comment) VALUES
(2, 5, 'Fast and clean service. Good communication.');

INSERT INTO messages (job_id, sender_id, body) VALUES
(1, 2, 'Hi, the leak is under the kitchen sink. Please bring pipe tape if needed.'),
(1, 3, 'Noted. I can come at the scheduled time and will confirm the final cost after checking.'),
(2, 4, 'Cleaning completed. I added RM5 for extra stain removal as discussed.'),
(2, 2, 'Confirmed, thank you for the quick work.');
