-- Add new field and index to store object and object version UUIDs
alter table `object`
  add column `uuid` varchar(36) after `id`,
  add unique index `uuid` (`uuid`);

alter table `object_version`
  add column `uuid` varchar(36) after `id`,
  add unique index `uuid` (`uuid`);

-- Populate UUIDs using MySQL
-- These aren't the most random, but this will do for now...
update `object` set `uuid` = uuid();
update `object_version` set `uuid` = uuid();
