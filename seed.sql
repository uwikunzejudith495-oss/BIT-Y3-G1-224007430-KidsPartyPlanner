USE kids_party_planner;

-- SAMPLE VENDORS
INSERT INTO vendors (name, service_type, price, contact_email)
VALUES
('Sweet Moments Bakery', 'Birthday Cakes', 35000, 'sweetmoments@example.com'),
('Happy Decorations', 'Party Decorations', 50000, 'happydecor@example.com'),
('Little Stars Events', 'Party Venue', 100000, 'littlestars@example.com'),
('Colorful Balloons', 'Balloons and Gifts', 20000, 'colorful@example.com'),
('Kids Fun Catering', 'Food and Drinks', 75000, 'kidscatering@example.com');

-- SAMPLE BOOKINGS ARE CREATED AFTER A PARENT REGISTERS
-- This avoids inserting bookings without valid parent and party IDs.