drop view solar_in;
create view solar_in as
with solar as (
	select
		rate_id,
		extract('hour' from a.interval) as hour,
		round(coalesce(watt, 0)::numeric, 0) as watt,
		extract('epoch' from a.interval) as interval,
		a.interval as datetime
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
			rate_id,
			avg(usage) as watt,
			to_timestamp((floor((extract('epoch' from datetime) / 900 )) * 900))::timestamp without time zone as interval
		from
			(select
				*
			from
				consumption
			where
				dev_id = 3
			and
				rate_id in (1, 6, 7)
			) as a
		group by
			interval, rate_id, direction
		order by interval
		) as b
	on
		a.interval::timestamp without time zone = b.interval
	order by datetime desc
)
select
	coalesce(b.watt, 0) as north,
	coalesce(c.watt, 0) as south,
	a.watt as total,
	a.hour,
	a.interval,
	a.datetime
from
	(select * from solar where coalesce(rate_id, 1) = 1) as a
left join
	(select * from solar where rate_id = 6) as b
on
	a.interval = b.interval
left join
	(select * from solar where rate_id = 7) as c
on
	a.interval = c.interval;
grant all on solar_in to logger;