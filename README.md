Energy logging (P1 meter and solar)
===================================

The following code is very quick and dirty, but works fine for my purposes.

**Only PR's are accepted. I won't be answering to issues**

## Database

Everything is saved in a PostgreSQL database. The database scheme used can be found in `database.sql`.

## Analytics

The analytics are based on database views which can be found in `views`:

**All aggregations are in a 15 minute resolution**

- *electricity_out.sql* aggregates the overcapacity of solar energy delivered back.
- *electricity_in.sql* aggregates the consumed electricity from the supplier.
- *solar_in.sql* aggregates the generated solar energy.
- *solar_total.sql* aggregates the average solar panel performance in watt peak.
- *gas.sql* aggregates the gas usage.

Additionally:

- *electricity_saldo.sql* aggregates the saldo of the solar energy and consumed electricity per day.
- *week.sql* aggregates everything on week level.

## Frontend

The web frontend can be found in the `web` folder. The frontend is a quick and dirty HTML / PHP solution to show various highcharts.

*Make sure to change the database settings in the PHP files.*

## Logging clients

To actually log the information from the p1 meter and the solar panels, the scripts in the `clients` folder are used.
*Make sure to change the various settings in the python files.*

- `p1read.py` runs as a daemon reading the Raspberry Pi serial interface.
- `solar.py` runs as a daemon reading the Solar inverter Omnik API.

- `pgsql.py` should be run periodically to fill the database with the cached queries.

<img src="https://raw.githubusercontent.com/CurlyMoo/energylogging/master/img/frontend1.jpg" alt="Final result" title="Final result" />
<img src="https://raw.githubusercontent.com/CurlyMoo/energylogging/master/img/frontend2.jpg" alt="Final result" title="Final result" />