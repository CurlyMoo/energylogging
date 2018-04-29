create table device_types (
  type_id serial not null primary key,
  name varchar(11) not null unique
);

create table devices (
  dev_id serial not null primary key,
  name character(34) not null unique,
  type_id integer not null references device_types(type_id) on delete set null on update cascade
);

create table rate_types (
  rate_id serial not null primary key,
  name varchar(25) not null
);

create table consumption (
  log_id serial8 not null primary key,
  dev_id integer not null references devices(dev_id) on delete set null on update cascade,
  rate_id integer not null references rate_types(rate_id) on delete set null on update cascade,
  usage double precision not null,
  datetime timestamp without time zone not null default now(),
  direction integer
);

insert into device_types (type_id, name) values
	(1, 'electricity'),
	(2, 'gas'),
	(3, 'solar');

/*
 * Make sure to add your own device ID's here
 */
insert into devices (dev_id, name, type_id) values
	(1, 'XXXXX', 1),
	(2, 'XXXXX', 2),
	(3, 'XXXXX', 3);

/*
 * Adapt this to your situation
 */
insert into rate_types (rate_id, name) VALUES
	(1, 'both'),
	(2, 'low'),
	(3, 'high'),
	(4, 'total'),
	(5, 'day'),
	(6, 'north'),
	(7, 'south');