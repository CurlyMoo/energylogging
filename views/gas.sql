create view gas as
select
	extract('hour' from a.interval) as hour,
	round(coalesce(m3, 0)::numeric, 3) as m3,
	extract('epoch' from a.interval) as interval
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
		((max(n_usage)-min(usage))) as m3,
		to_timestamp((floor((extract('epoch' from datetime) / 900 )) * 900))::timestamp without time zone as interval
	from
		(select
			lead(usage) over (partition by rate_id order by datetime) as n_usage,
			*
		from
			consumption
		where
			rate_id = 1
		and
			dev_id = 2
		) as a
	where
		n_usage is not null	
	group by
		interval, rate_id
	) as b
on
	a.interval::timestamp without time zone = b.interval;
grant all on gas to logger;