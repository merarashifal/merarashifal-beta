-- Migration: 002_upgrade_af_remedies.sql
-- Description: Upgrade af_remedies table schema with SEO, AI search (GEO), and structured JSON fields
-- Compatibility: MariaDB 10.2+ / MySQL 8.0+

-- 1. Remove duplicate Pisces / मीन records (IDs 881 to 960), retaining the canonical lower remedy_ids
DELETE r1 FROM `af_remedies` r1
INNER JOIN `af_remedies` r2 
WHERE r1.remedy_id > r2.remedy_id
  AND r1.sun_sign = r2.sun_sign
  AND r1.gender = r2.gender
  AND r1.issue = r2.issue
  AND r1.language = r2.language;

-- 2. Add new SEO, ruling planet, AI quick answer, and JSON schema columns
ALTER TABLE `af_remedies`
  ADD COLUMN IF NOT EXISTS `slug` VARCHAR(220) NULL AFTER `language`,
  ADD COLUMN IF NOT EXISTS `issue_slug` VARCHAR(80) NULL AFTER `slug`,
  ADD COLUMN IF NOT EXISTS `ruling_planet` VARCHAR(80) NULL AFTER `issue_slug`,
  ADD COLUMN IF NOT EXISTS `meta_title` VARCHAR(160) NULL AFTER `ruling_planet`,
  ADD COLUMN IF NOT EXISTS `meta_description` VARCHAR(255) NULL AFTER `meta_title`,
  ADD COLUMN IF NOT EXISTS `quick_answer` TEXT NULL AFTER `meta_description`,
  ADD COLUMN IF NOT EXISTS `action_checklist_json` JSON NULL AFTER `quick_answer`,
  ADD COLUMN IF NOT EXISTS `faq_json` JSON NULL AFTER `action_checklist_json`;

-- 3. Add composite index for slug and language lookup
CREATE INDEX IF NOT EXISTS `idx_slug_lang` ON `af_remedies` (`slug`, `language`);

-- 4. Ensure composite index for sign, gender, issue, language lookup exists
CREATE INDEX IF NOT EXISTS `idx_af_remedies_lookup` ON `af_remedies` (`sun_sign`, `gender`, `issue`, `language`);
