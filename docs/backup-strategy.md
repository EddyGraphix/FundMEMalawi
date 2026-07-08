# Backup Strategy

## Schedule

| Backup Type | Frequency | Retention | Contents |
|---|---|---|---|
| Database | Daily | 30 days | Full SQL dump |
| Media | Daily | 30 days | `storage/app/public` |
| Code | Per-deploy | 10 latest releases | Git + released artifacts |
| Configuration | Weekly | 90 days | `.env`, config files |

## Commands

### Database
```bash
# SQLite
cp database/database.sqlite backups/db/$(date +%Y%m%d).sqlite

# PostgreSQL
pg_dump fundme_malawi > backups/db/$(date +%Y%m%d).sql
```

### Media
```bash
# Using Spatie Media Library default disk
rsync -a storage/app/public/ backups/media/daily/$(date +%Y%m%d)/
```

## Disaster Recovery

1. **Database corruption**: Restore most recent daily backup.
2. **Media loss**: Restore from media backup and re-run `php artisan medialibrary:regenerate`.
3. **Full server failure**: Provision new server, deploy latest release, restore DB + media.

## Restoration

```bash
# Restore database
cp backups/db/YYYYMMDD.sqlite database/database.sqlite

# Restore media
rsync -a backups/media/daily/YYYYMMDD/ storage/app/public/

# Re-generate media conversions
php artisan medialibrary:regenerate

# Re-link storage
php artisan storage:link
```

## Monitoring

- Backup job logs to `logs/backup.log` channel
- Failed backups alert via Sentry
- Weekly backup integrity check via Artisan command
