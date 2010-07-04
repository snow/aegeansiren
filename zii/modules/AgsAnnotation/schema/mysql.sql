create table `AgsAnnotation` (
	`id`				bigint unsigned auto_increment primary key,
	`subtype`			char(20) not null,
	`desc`				text,
	`ownerId`			int unsigned not null,
	`containerId`		int unsigned not null,
	`containerClass`	char(20) not null,
	`accessId`			tinyint not null,
	`created`			int unsigned not null,
	`updated`			int unsigned not null,
	`status`			enum('active','pending','banned') not null,
	`enabled`			bool not null,	
	
	key `subtype` (`subtype`),	
	key `ownerId` (`ownerId`),
	key `containerId` (`containerId`),
	key `containerClass` (`containerClass`),
	key `accessId` (`accessId`),
	key `created` (`created`),
	key `updated` (`updated`),
	key `status` (`status`),
	key `enabled` (`enabled`)
) engine = MyISAM;