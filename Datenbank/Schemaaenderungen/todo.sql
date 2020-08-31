ALTER TABLE `users`
ADD `userAutoresponse` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `users`
ADD `userAutoresponseText` longtext NOT NULL;