-- Add lifegroup_id to lifegroup_events to link events to a specific lifegroup
USE churchapp;

ALTER TABLE lifegroup_events
  ADD COLUMN lifegroup_id INT NULL AFTER church_id,
  ADD INDEX idx_lifegroup_id (lifegroup_id),
  ADD CONSTRAINT fk_lifegroup_events_lifegroup
    FOREIGN KEY (lifegroup_id) REFERENCES lifegroups(id) ON DELETE SET NULL;


