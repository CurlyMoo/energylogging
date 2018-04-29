
drop view week cascade;
create view week as
with elec_out as (
	with foo as (
		select
			a.year,
			a.week,
			round(coalesce(usage, 0)::numeric, 0) as usage,
			n_usage,
			direction,
			rate_id
		from
			(select
				to_char(a, 'YYYY')::int as year,
				to_char(a, 'WW')::int as week
			from
				generate_series(
					(select to_timestamp((floor((extract('epoch' from min(datetime)) / 604800 )) * 604800)) from consumption),
					(select to_timestamp((floor((extract('epoch' from max(datetime)) / 604800 )) * 604800)) from consumption),
					interval '1 week'
				) as a
			) as a
		left join
			(select
				min(usage) as usage,
				max(n_usage) as n_usage,
				to_char(datetime - interval '1 day', 'YYYY')::int as year,
				to_char(datetime - interval '1 day', 'WW')::int as week,
				direction,
				rate_id
			from
				(select
					lead(usage) over (partition by rate_id, direction order by datetime) as n_usage,
					*
				from
					consumption
				where
					rate_id in (2, 3)
				and
					direction = 1
				) as a
			where
				n_usage is not null	
			group by
				year, week, rate_id, direction
			) as b
		on
			a.year = b.year
		and
			a.week = b.week
	)
	select
		a.year,
		a.week,
		a.usage+b.usage as usage,
		a.n_usage+b.n_usage as n_usage
	from
		foo as a
	inner join
		foo as b
	on
		a.week = b.week
	and
		a.year = b.year
	and
		a.rate_id = 2
	and
		b.rate_id = 3
),
foo as (
	select
		*,
		lead(usage) over (partition by a.dev_id, a.rate_id, a.direction order by a.year, a.week) as n_usage
	from
		(select
			year,
			week,
			dow,
			dev_id,
			rate_id,
			case when usage is null then
				((n_usage+p_usage)/2)
			else
				usage
			end as usage,
			case when datetime is null then
				to_timestamp((extract('epoch' from n_datetime)+extract('epoch' from p_datetime))/2)
			else
				datetime
			end as datetime,
			direction
		from
			(select
				year,
				week,
				dow,
				dev_id,
				rate_id,
				usage,
				datetime,
				direction,
				first_value(datetime) over (partition by value_partition, a.dev_id, a.rate_id, a.direction order by a.year, a.week) as n_datetime,
				lead(datetime) over (partition by a.dev_id, a.rate_id, a.direction order by a.year, a.week) as p_datetime,
				first_value(usage) over (partition by value_partition, a.dev_id, a.rate_id, a.direction order by a.year, a.week) as n_usage,
				lead(usage) over (partition by a.dev_id, a.rate_id, a.direction order by a.year, a.week) as p_usage
			from
				(select
					a.year,
					a.week,
					1 as dow,
					a.dev_id,
					a.rate_id,
					b.usage,
					b.datetime,
					a.direction,
					sum(case when usage is null then 0 else 1 end) over (partition by a.dev_id, a.rate_id, a.direction order by a.year, a.week) as value_partition
				from
					(select
						*,
						unnest(array[1, 2, 3]) as dev_id
					from
						(select
							*,
							unnest(array[0, 1]) as direction
						from
							(select
								unnest(array[1, 2, 3, 4]) as rate_id,
								extract('week' from a) as week,
								extract('year' from a) as year
							from
								generate_series(
									(select min(datetime) from consumption),
									(select max(datetime) + interval '1 week' from consumption),
									interval '1 week'
								) as a
							) as a
						) as a
					) as a
				left join
					(select
						direction,
						dev_id,
						extract('dow' from datetime) as dow,
						extract('week' from datetime) as week,
						extract('year' from datetime) as year,
						max(datetime) as datetime,
						max(usage) as usage,
						rate_id
					from
						consumption
					where
						extract('dow' from datetime) = 1
					group by
						year, week, dow, dev_id, rate_id, direction
					order by
						year, week, dow, dev_id, rate_id, direction
					) as b
				on
					a.week = b.week
				and
					a.year = b.year
				and
					a.rate_id = b.rate_id
				and
					a.direction = b.direction
				and
					a.dev_id = b.dev_id
				) as a
			) as a
		) as a	
	)
select
	distinct on(a.week, a.year)
	*
from
	(select
 		a.datetime,
		a.week,
		a.year,
		round(e.n_usage::numeric, 2) as elec_hoog_uit,
		round((e.n_usage-e.usage)::numeric, 2) as elec_hoog_uit_diff,
		round(a.n_usage::numeric, 2) as elec_hoog_in,
		round((a.n_usage-a.usage)::numeric, 2) as elec_hoog_in_diff,
		round(coalesce(b.n_usage, 0)::numeric, 2) as elec_laag_in,
		round(coalesce(b.n_usage-b.usage, 0)::numeric, 2) as elec_laag_in_diff,
		round(c.n_usage::numeric, 2) as zon_in,
		round((c.n_usage-c.usage)::numeric, 2) as zon_diff,
		round(d.n_usage::numeric, 2) as gas_in,
		round((d.n_usage-d.usage)::numeric, 2) as gas_diff,
		round((((a.n_usage-a.usage)+coalesce(b.n_usage-b.usage, 0)))::numeric, 2) as elec_bruto,
		round((((a.n_usage-a.usage)+coalesce(b.n_usage-b.usage, 0))-coalesce((e.n_usage-e.usage), 0))::numeric, 2) as elec_netto
	from
		foo as a
	left join
		foo as b
	on
		--
		-- Electriciteit laag verbruikt
		--
		a.year = b.year and a.week = b.week and b.rate_id = 2 and b.dev_id = 1 and b.direction = 0
	left join
		foo as c
	on
		--
		-- Zon totaal
		--
		a.year = c.year and a.week = c.week and c.dev_id = 3 and c.rate_id = 4 and c.direction = 1
	left join
		foo as d
	on
		--
		-- Gas totaal
		--
		a.year = d.year and a.week = d.week and d.dev_id = 2 and d.rate_id = 1 and d.direction = 0
	left join
		elec_out as e
	on
		--
		-- Electriciteit uit
		--
		a.year = e.year and a.week = e.week
	where
		--
		-- Electriciteit hoog verbruikt
		--
		a.rate_id = 3 and a.dev_id = 1 and a.direction = 0		
	) as a
where
	extract('year' from now()) > year
or
	(
		extract('year' from now()) = year
	and
		extract('week' from now()) > week
	)
order by
	year, week;
grant all on week to logger;
select * from week;
