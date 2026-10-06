-- Migration: 050_standalone_roles
-- Eliminacode Upgrade is now a standalone app with two roles: 'admin' and
-- 'operator'. The role column becomes free text (the old POS enum had waiter,
-- cashier, kitchen, till). Staff users inherited from the POS are disabled,
-- never deleted; the admin can re-enable one as Operatore in Amministrazione › Utenti.

ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'operator';
UPDATE users SET active = 0 WHERE role NOT IN ('admin', 'operator');
