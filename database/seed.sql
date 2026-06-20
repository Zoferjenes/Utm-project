USE fixit_arcade;

INSERT INTO users (name, email, password_hash, role, phone) VALUES
('Arcade Admin', 'admin@fixit.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'admin', '010-0000000'),
('Fiyandha Customer', 'customer@fixit.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'customer', '011-1111111'),
('Ali Plumber', 'provider@fixit.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'provider', '012-2222222'),
('Sara Cleaner', 'sara@fixit.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'provider', '013-3333333'),
('Omar Electrician', 'omar@fixit.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'provider', '014-4444444');

INSERT INTO service_categories (name, description, icon) VALUES
('Plumbing', 'Leak repairs, toilet repair, pipe installation, water heater support.', 'pipe'),
('Electrical', 'Fan installation, wiring checks, socket repair, light installation.', 'bolt'),
('Cleaning', 'Deep cleaning, move-out cleaning, room sanitizing.', 'sparkle'),
('Gardening', 'Lawn care, plant trimming, simple landscaping.', 'leaf'),
('AC Service', 'AC cleaning, gas top-up, filter service, cooling diagnostics.', 'snowflake');

INSERT INTO provider_profiles (user_id, bio, location, base_rate, photo_url, is_verified, kyc_doc_url) VALUES
(3, 'Experienced plumber for student apartments and small household repairs.', 'Taman Universiti, Skudai', 50.00, '/provider-ali.svg', 1, 'mock-kyc/ali-plumber.pdf'),
(4, 'Cleaner specializing in weekly room cleaning and move-out deep cleaning.', 'Kangkar Pulai, Johor', 40.00, '/provider-sara.svg', 1, 'mock-kyc/sara-cleaner.pdf'),
(5, 'Electrical technician for fans, sockets, lights, and small rewiring work.', 'Mutiara Rini, Johor', 60.00, '/provider-omar.svg', 0, 'mock-kyc/omar-electrician.pdf');

INSERT INTO provider_categories (provider_id, category_id) VALUES
(1, 1),
(1, 5),
(2, 3),
(2, 4),
(3, 2);

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
