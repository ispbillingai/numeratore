-- Migration: 051_azure_colors
-- The app's palette is now azure: new services default to azure, and the
-- seeded "Banco" service (still the old red) becomes azure too.

ALTER TABLE queue_services ALTER COLUMN color SET DEFAULT '#0284c7';
UPDATE queue_services SET color = '#0284c7' WHERE color = '#e74c3c';
