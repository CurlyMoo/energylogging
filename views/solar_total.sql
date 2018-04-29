drop view solar_total;
create view solar_total as
select
	extract('hour' from a.interval) as hour,
	round(coalesce(watt, 0)::numeric, 0) as watt,
	extract('epoch' from a.interval) as interval,
	a.interval as datetime,
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
		((max(usage)-min(n_usage))*1000) as watt,
		to_timestamp((floor((extract('epoch' from datetime) / 900 )) * 900))::timestamp without time zone as interval,
		direction
	from
		(select
			lag(usage) over (partition by rate_id, direction order by datetime) as n_usage,
			lag(extract('day' from datetime)) over (partition by rate_id, direction order by datetime) as n_day,
			extract('day' from datetime) as day,
			*
		from
			consumption
		where
			dev_id = 3
		and
			rate_id = 5
		) as a
	where
		usage > n_usage
	and
		day = n_day
	group by
		interval, rate_id, direction
	) as b
on
	a.interval::timestamp without time zone = b.interval;
grant all on solar_total to logger;