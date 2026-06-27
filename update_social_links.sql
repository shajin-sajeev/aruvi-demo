-- Update or insert real social media links for Aruvi on the Cliff
-- Run this in phpMyAdmin to update the live database without needing a re-seed

INSERT INTO `social_links` (`platform`, `url`, `is_active`, `created_at`, `updated_at`)
VALUES
  ('Facebook',  'https://www.facebook.com/share/18o4HeYhbZ/',                              1, NOW(), NOW()),
  ('Threads',   'https://www.threads.com/@aruvi_onthecliff',                               1, NOW(), NOW()),
  ('Twitter',   'https://x.com/Aruviontheclirf',                                           1, NOW(), NOW()),
  ('Instagram', 'https://www.instagram.com/aruvi_onthecliff?igsh=MTBzOHFyb2c1YnozdQ==',   1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `url`        = VALUES(`url`),
  `is_active`  = 1,
  `updated_at` = NOW();
