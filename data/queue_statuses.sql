ALTER TABLE `assessment_runs` MODIFY COLUMN `status` enum('queued', 'running', 'complete', 'timeout', 'error') NOT NULL DEFAULT 'queued';