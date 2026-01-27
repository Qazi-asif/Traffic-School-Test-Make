#!/bin/bash

# Create bulk transmissions for all completed enrollments without transmissions
# Usage: ./create-bulk-transmissions.sh [limit]

LIMIT=${1:-0}
HOST="172.29.128.1"
USER="root"
DB="schoolplatform"

if [ "$LIMIT" -eq 0 ]; then
    echo "Creating transmissions for ALL completed enrollments..."
    mysql -h $HOST -u $USER $DB << 'SQL'
INSERT INTO state_transmissions (enrollment_id, state, system, status, payload_json, created_at, updated_at)
SELECT 
    uce.id,
    'FL',
    'FLHSMV',
    'pending',
    JSON_OBJECT('enrollment_id', uce.id),
    NOW(),
    NOW()
FROM user_course_enrollments uce
WHERE uce.completed_at IS NOT NULL
AND uce.id NOT IN (
    SELECT DISTINCT enrollment_id FROM state_transmissions WHERE state = 'FL'
);
SQL
else
    echo "Creating transmissions for $LIMIT completed enrollments..."
    mysql -h $HOST -u $USER $DB << SQL
INSERT INTO state_transmissions (enrollment_id, state, system, status, payload_json, created_at, updated_at)
SELECT 
    uce.id,
    'FL',
    'FLHSMV',
    'pending',
    JSON_OBJECT('enrollment_id', uce.id),
    NOW(),
    NOW()
FROM user_course_enrollments uce
WHERE uce.completed_at IS NOT NULL
AND uce.id NOT IN (
    SELECT DISTINCT enrollment_id FROM state_transmissions WHERE state = 'FL'
)
LIMIT $LIMIT;
SQL
fi

echo "✓ Transmissions created successfully"
echo "Run: php artisan queue:work"
