-- Rename the object encryption key field to fit better with the description in architecture.md
ALTER TABLE `object` CHANGE encryptionKey sessionKey blob;
ALTER TABLE `object` CHANGE encryptionKeyIv sessionKeyIv blob;
ALTER TABLE `object_version` CHANGE encryptionKey sessionKey blob;
ALTER TABLE `object_version` CHANGE encryptionKeyIv sessionKeyIv blob;
