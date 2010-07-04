create table `AgsUser` (
	`id`			int unsigned auto_increment primary key,
	`email`			char(40) not null unique,
	`username`		char(12) not null unique,
	`name`			char(30) charset utf8 not null,	
	
	`passwordHash`	char(32) not null,
	`salt`			char(32) not null,
	
	`subtype`		char(32) not null,
	`accessId`		tinyint not null,
	`created`		int unsigned not null,
	`updated`		int unsigned not null,
	`status`		enum('active','pending','banned') not null,
	`enabled`		bool not null,

	key `name` (`name`),
	key `subtype` (`subtype`),
	key `accessId` (`accessId`),
	key `created` (`created`),
	key `updated` (`updated`),
	key `status` (`status`),
	key `enabled` (`enabled`)
) engine = MyISAM;