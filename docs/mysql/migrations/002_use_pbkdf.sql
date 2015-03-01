-- Add new fields for holding the settings for key derivation
ALTER TABLE `key`
  ADD COLUMN `kdf_salt` BLOB NULL DEFAULT NULL AFTER `user_id`,
  ADD COLUMN `kdf_iterations` INT(11) NULL DEFAULT NULL AFTER `kdf_salt`;

ALTER TABLE `user`
  ADD COLUMN `otpSalt` BLOB NULL DEFAULT NULL AFTER `otpIv`;

ALTER TABLE `user`
  ADD COLUMN `accountKeyPhraseSalt` BLOB NULL DEFAULT NULL AFTER `accountKeyPhrase`,
  ADD COLUMN `accountKeyPhraseIterations` INT(11) NULL DEFAULT NULL AFTER `accountKeyIv`;
