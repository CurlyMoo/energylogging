
/*
 * Calculates the used electricity
 */
create view electricity_in as
select
	extract('hour' from a.interval) as hour,
	round(coalesce(watt, 0)::numeric, 0) as watt,
	extract('epoch' from a.interval) as interval,
	direction
from
	(select
		a as interval
	from
		generate_series(
			(select to_timestamp((floor((extract('epoch' from min(datetime)) / 900 )) * 900)) from consumption),
			(select to_timestamp((floor((extract('epoch' from max(datetime)) / 900 )) * 900)) from consumption),
			interval '15 minute'
		) as a
	) as a
left join
	(select
		((max(n_usage)-min(usage))*1000) as watt,
		to_timestamp((floor((extract('epoch' from datetime) / 900 )) * 900))::timestamp without time zone as interval,
		direction
	from
		(select
			lead(usage) over (partition by rate_id, direction order by datetime) as n_usage,
			*
		from
			consumption
		where
			rate_id in (2, 3)
		and
			direction = 0
		) as a
	where
		n_usage is not null	
	group by
		interval, rate_id, direction
	) as b
on
	a.interval::timestamp without time zone = b.interval;
grant all on electricity_in to logger;