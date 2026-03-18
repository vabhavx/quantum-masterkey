#!/bin/bash
# ═══════════════════════════════════════════════════════
# OPERATION GRAPESHOT SALVO — MASTER BATCH LAUNCHER
# ═══════════════════════════════════════════════════════

LOG="/tmp/salvo_cron.log"
DATA_DIR="/Users/vaibhavchhimpa/AntiPalantir/salvo_data"
LEADS="/Users/vaibhavchhimpa/Downloads/untitled folder/Burnthelies/grapshot-salvo/us_users_sample.csv"
SENT="$DATA_DIR/sent.txt"

echo "$(date '+[%Y-%m-%d %H:%M:%S]') ══ MASTER BATCHING START ══" >> "$LOG"

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

# 1. Prepare unique batches for each account (88 per slot)
python3 <<EOF
import csv
import os
import json

data_dir = "$DATA_DIR"
leads_path = "$LEADS"
sent_path = "$SENT"
per_account = 10
accounts = $(printf '%s\n' "${ACCOUNTS[@]}" | jq -R . | jq -s .)

# Load sent emails
sent_emails = set()
if os.path.exists(sent_path):
    with open(sent_path, 'r') as f:
        for line in f:
            sent_emails.add(line.strip().lower())

# Extract next batch
to_send = []
needed = len(accounts) * per_account

with open(leads_path, 'r', encoding='utf-8', errors='ignore') as f:
    reader = csv.reader(f)
    for row in reader:
        if len(row) < 4: continue
        email = row[3].strip().lower()
        if email and email not in sent_emails:
            to_send.append(row)
            sent_emails.add(email) # don't pick twice in same batch
            if len(to_send) >= needed:
                break

# Split and save to account-specific files
all_allocated = []
for i, acc in enumerate(accounts):
    batch = to_send[i * per_account : (i + 1) * per_account]
    if not batch: continue
    
    acc_file = os.path.join(data_dir, f"leads_{acc}.csv")
    with open(acc_file, 'w', newline='') as f:
        writer = csv.writer(f)
        writer.writerows(batch)
    
    for row in batch:
        all_allocated.append(row[3].strip().lower())

# PRE-EMPTIVE CLAIM: Mark as sent immediately to block other slots
if all_allocated:
    with open(sent_path, 'a') as f:
        for email in all_allocated:
            f.write(email + "\n")
EOF

# 2. Launch workers
for acc in "${ACCOUNTS[@]}"; do
    acc_file="$DATA_DIR/leads_$acc.csv"
    if [ -f "$acc_file" ]; then
        docker exec mautic_app php /var/www/html/salvo.php "$acc" "$acc_file" >> "$LOG" 2>&1 &
        # GHOST OMEGA STAGGER: Sleep 10 minutes between launching each account
        sleep 600
    fi
done

wait
echo "$(date '+[%Y-%m-%d %H:%M:%S]') ══ MASTER BATCHING COMPLETE ══" >> "$LOG"
