create table `AgsUniqueCharKeyMax` (
	`tableName`		char(30),
	`columnName`	char(30),
	`max`			char(12),
	
	primary key (`tableName`,`columnName`)
) engine = MyISAM;