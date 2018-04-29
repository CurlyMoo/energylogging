
drop view electricity_saldo;
create view electricity_saldo as
with foo as (
	select
		*,
		lag(usage) over (partition by dev_id, rate_id, direction order by year, day) as p_usage
	from
		(select
			direction,
			dev_id,
			to_char(datetime, 'YYYY')::int as year,
			to_char(datetime, 'YYYYMMDD')::int as day,
			max(datetime) as datetime,
			max(usage) as usage,
			rate_id
		from
			consumption
		group by
			year, day, dev_id, rate_id, direction
		order by
			year, day, dev_id, rate_id, direction
		) as a
	order by
		year, day, dev_id, rate_id, direction
)
select
	distinct on(a.year, a.day)
	extract('epoch' from datetime) as interval,
	*
from
	(select
		a.datetime,
		a.year,
 		a.day,
		round((((a.usage-a.p_usage)+coalesce(b.usage-b.p_usage, 0))-coalesce((e.usage-e.p_usage), 0))::numeric, 2)*1000 as saldo
	from
		foo as a
	left join
		foo as b
	on
		--
		-- Electriciteit laag verbruikt
		--
		a.day = b.day and b.dev_id = 1 and b.rate_id = 2 and b.direction = 0
	left join
		foo as e
	on
		--
		-- Electriciteit uit
		--
		a.day = e.day and e.dev_id = 1 and e.rate_id in (2, 3) and e.direction = 1
	where
		--
		-- Electriciteit hoog verbruikt
		--
		a.rate_id = 3 and a.dev_id = 1 and a.direction = 0
	) as a
where
	saldo is not null
order by
	year, day;
grant all on electricity_saldo to logger;
select * from electricity_saldo;