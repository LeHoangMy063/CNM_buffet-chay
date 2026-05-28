# MongoDB analytics mirror

MongoDB is used as an optional analytics mirror. MySQL remains the main transactional database.

## Compass access

```text
Mongo URI inside web container: mongodb://mongo:27017
MongoDB Compass URI on host: mongodb://localhost:27018
Database: buffet_chay_analytics
Collection: bao_cao_doanh_thu
```

## Auto sync a report

1. Open `Quan ly -> Bao cao doanh thu`.
2. Choose a date range, for example `2026-04-01` to `2026-04-30`.
3. Click `Xem bao cao`.
4. The app automatically upserts the report snapshot into MongoDB.
5. Open MongoDB Compass and connect:

```text
mongodb://localhost:27018
```

Then check:

```text
buffet_chay_analytics.bao_cao_doanh_thu
```

The app stores one upserted report snapshot per date range using `ma_bao_cao`.
