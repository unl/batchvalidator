ALTER TABLE `assessment_runs` ADD `run_type` enum('user', 'auto') NOT NULL DEFAULT 'user';
ALTER TABLE `assessment_runs` ADD `status` enum('queued', 'running', 'complete') NOT NULL DEFAULT 'queued';