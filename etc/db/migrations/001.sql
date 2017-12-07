INSERT INTO `setting` (`name`, `user_id`, `value`) VALUES
('gdriveRootFolderName', 1, 'songbook');
ALTER TABLE `content` CHANGE `type` `type` ENUM('header', 'inline', 'link', 'gdrive_cloud_file') DEFAULT 'inline';

UPDATE `content` set is_favorite=0 WHERE is_favorite IS NULL;
ALTER TABLE `content` CHANGE is_favorite is_favorite TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE content ADD file_name VARCHAR(400);
ALTER TABLE `content` ADD mime_type VARCHAR(255);
