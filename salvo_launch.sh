#!/bin/bash
# ═══════════════════════════════════════════════════════
# OPERATION GRAPESHOT SALVO — PARALLEL LAUNCHER
# Fires all 10 Gmail accounts simultaneously
# Called by Mac crontab 4x daily
# ═══════════════════════════════════════════════════════

LOG="/Users/vaibhavchhimpa/AntiPalantir/salvo_data/cron.log"
echo "$(date '+[%Y-%m-%d %H:%M:%S]') ══ SLOT FIRED ══" >> "$LOG"

ACCOUNTS=(
    "drrichachaturvedii@gmail.com"
    "pa.sharma.9966@gmail.com"
    "ch.vabhav@gmail.com"
    "vabhavv.x@gmail.com"
    "chuggrivers@gmail.com"
    "chronossflex@gmail.com"
    "ediitoriaa@gmail.com"
    "eeventiss@gmail.com"
    "jtenzerr@gmail.com"
    "farkhadovmalik@gmail.com"
)

for acc in "${ACCOUNTS[@]}"; do
    docker exec mautic_app php /var/www/html/salvo.php "$acc" >> "$LOG" 2>&1 &
done

wait
echo "$(date '+[%Y-%m-%d %H:%M:%S]') ══ SLOT COMPLETE ══" >> "$LOG"
