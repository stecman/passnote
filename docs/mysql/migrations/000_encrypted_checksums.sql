ALTER TABLE `object`
  CHANGE COLUMN `checksum` `checksum` BLOB NOT NULL COMMENT 'Encrypted checksum of the plain text value of `content`.';

ALTER TABLE `object_version`
  CHANGE COLUMN `checksum` `checksum` BLOB NOT NULL;
