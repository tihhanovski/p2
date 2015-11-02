ALTER TABLE email CHANGE ID id INT NOT NULL AUTO_INCREMENT;
ALTER TABLE email CHANGE mdCreatorID mdCreatorId INT NULL DEFAULT NULL;
ALTER TABLE email CHANGE mdUpdaterID mdUpdaterId INT NULL DEFAULT NULL;

ALTER TABLE `webuser` CHANGE `ID` `id` INT NOT NULL  AUTO_INCREMENT;
ALTER TABLE `userstats` CHANGE `ID` `id` INT NOT NULL  AUTO_INCREMENT;

/* 0:56:04  Fobos */ ALTER TABLE `tipsystem` CHANGE `ID` `id` INT(11)  NOT NULL  AUTO_INCREMENT;
/* 0:56:24  Fobos */ ALTER TABLE `tipsystem` CHANGE `mdCreatorID` `mdCreatorId` INT(11)  NULL  DEFAULT NULL;
/* 0:56:26  Fobos */ ALTER TABLE `tipsystem` CHANGE `mdUpdaterID` `mdUpdaterId` INT(11)  NULL  DEFAULT NULL;
/* 0:57:03  Fobos */ ALTER TABLE `tipshown` CHANGE `ID` `id` INT(11)  NOT NULL  AUTO_INCREMENT;
/* 0:57:05  Fobos */ ALTER TABLE `tipshown` CHANGE `tipID` `tipId` INT(11)  NOT NULL  DEFAULT '0';
/* 0:57:07  Fobos */ ALTER TABLE `tipshown` CHANGE `userID` `userId` INT(11)  NOT NULL  DEFAULT '0';
/* 0:58:05  Fobos */ ALTER TABLE `softwareissue` CHANGE `mdCreatorID` `mdCreatorId` INT(11)  NULL  DEFAULT NULL;
/* 0:58:07  Fobos */ ALTER TABLE `softwareissue` CHANGE `mdUpdaterID` `mdUpdaterId` INT(11)  NULL  DEFAULT NULL;
/* 0:58:33  Fobos */ ALTER TABLE `role` CHANGE `ID` `id` INT(11)  NOT NULL  AUTO_INCREMENT;
/* 0:58:52  Fobos */ ALTER TABLE `robject` CHANGE `ID` `id` INT(11)  NOT NULL  AUTO_INCREMENT;
/* 0:58:54  Fobos */ ALTER TABLE `robject` CHANGE `typeID` `typeId` INT(11)  NOT NULL  DEFAULT '1';
/* 0:59:12  Fobos */ ALTER TABLE `rmodule` CHANGE `ID` `id` INT(11)  NOT NULL  DEFAULT '0';
/* 0:59:36  Fobos */ ALTER TABLE `objlog` CHANGE `ID` `id` INT(11)  NOT NULL  AUTO_INCREMENT;
/* 1:04:29  Fobos */ ALTER TABLE `menupart` CHANGE `ID` `id` INT(11)  NOT NULL  DEFAULT '0';
/* 1:05:20  Fobos */ ALTER TABLE `message` CHANGE `ID` `id` INT(11)  NOT NULL  AUTO_INCREMENT;
/* 1:05:23  Fobos */ ALTER TABLE `message` CHANGE `replyToID` `replyToId` INT(11)  NULL  DEFAULT NULL;
/* 1:06:51  Fobos */ ALTER TABLE `objectlink` CHANGE `mdCreatorID` `mdCreatorId` INT(11)  NULL  DEFAULT NULL;
/* 1:06:52  Fobos */ ALTER TABLE `objectlink` CHANGE `mdUpdaterID` `mdUpdaterId` INT(11)  NULL  DEFAULT NULL;
/* 1:10:10  Fobos */ ALTER TABLE `state` CHANGE `ID` `id` INT(11)  NOT NULL  DEFAULT '0';

ALTER TABLE userrole CHANGE userID userId INT NULL DEFAULT NULL;
ALTER TABLE userrole CHANGE roleID roleId INT NULL DEFAULT NULL;
ALTER TABLE objectright CHANGE roleID roleId INT NOT NULL;
ALTER TABLE objectright CHANGE registryID registryId INT NOT NULL;