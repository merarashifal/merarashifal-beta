-- Migration: 001_upgrade_zodiac_compatibility.sql
-- Description: Upgrade zodiac_compatibility table schema with SEO, AI search (GEO), and structured JSON fields
-- Compatibility: MariaDB 10.2+ / MySQL 8.0+

ALTER TABLE `zodiac_compatibility`
  ADD COLUMN IF NOT EXISTS `slug` VARCHAR(200) NULL AFTER `language`,
  ADD COLUMN IF NOT EXISTS `element_pair` VARCHAR(60) NULL AFTER `slug`,
  ADD COLUMN IF NOT EXISTS `meta_title` VARCHAR(160) NULL AFTER `element_pair`,
  ADD COLUMN IF NOT EXISTS `meta_description` VARCHAR(255) NULL AFTER `meta_title`,
  ADD COLUMN IF NOT EXISTS `verdict_summary` TEXT NULL AFTER `meta_description`,
  ADD COLUMN IF NOT EXISTS `key_strength` TEXT NULL AFTER `verdict_summary`,
  ADD COLUMN IF NOT EXISTS `key_challenge` TEXT NULL AFTER `key_strength`,
  ADD COLUMN IF NOT EXISTS `faq_json` LONGTEXT NULL AFTER `key_challenge`,
  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `score_finance`;

-- Create index for URL slug lookup
CREATE INDEX IF NOT EXISTS `idx_slug_lang` ON `zodiac_compatibility` (`slug`, `language`);
