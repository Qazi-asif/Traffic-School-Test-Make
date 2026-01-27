-- Fix courses table - set state based on title patterns
UPDATE courses SET state = 'FL' WHERE state IS NULL AND (title LIKE '%florida%' OR title LIKE '%FL%' OR title LIKE '%FLHSMV%');
UPDATE courses SET state = 'Missouri' WHERE state IS NULL AND (title LIKE '%missouri%' OR title LIKE '%MO%');
UPDATE courses SET state = 'TX' WHERE state IS NULL AND (title LIKE '%texas%' OR title LIKE '%TX%');
UPDATE courses SET state = 'DE' WHERE state IS NULL AND (title LIKE '%delaware%' OR title LIKE '%DE%');
UPDATE courses SET state = 'CA' WHERE state IS NULL AND (title LIKE '%california%' OR title LIKE '%CA%');
UPDATE courses SET state = 'NV' WHERE state IS NULL AND (title LIKE '%nevada%' OR title LIKE '%NV%');

-- Enable transmission systems for courses
UPDATE courses SET tvcc_enabled = 1, ctsi_enabled = 1 WHERE state = 'CA';
UPDATE courses SET ntsa_enabled = 1 WHERE state = 'NV';
UPDATE courses SET ccs_enabled = 1 WHERE state NOT IN ('FL', 'CA', 'NV') AND state IS NOT NULL;

-- Fix florida_courses table - set state_code
UPDATE florida_courses SET state_code = 'FL' WHERE state_code IS NULL;

-- Verify the updates
SELECT 'courses table:' as table_name;
SELECT id, title, state, tvcc_enabled, ctsi_enabled, ntsa_enabled, ccs_enabled FROM courses;

SELECT 'florida_courses table:' as table_name;
SELECT id, title, state_code FROM florida_courses LIMIT 10;
