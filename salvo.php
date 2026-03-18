<?php
// ═══════════════════════════════════════════════════════
// OPERATION GRAPESHOT SALVO — COLLISION-FREE WORKER
// Usage: php salvo.php <gmail_address> <batch_file>
// ═══════════════════════════════════════════════════════

require '/var/www/html/vendor/autoload.php';

$accounts = [
    'drrichachaturvedii@gmail.com' => 'kuvznjkervrrdnqy',
    'pa.sharma.9966@gmail.com'     => 'owufodapiuumafvs',
    'ch.vabhav@gmail.com'          => 'vsddljadklnoeqxq',
    'vabhavv.x@gmail.com'          => 'pbeluhcqrqmhbmwp',
    'chuggrivers@gmail.com'        => 'hzdaxduytpzjfmwm',
    'chronossflex@gmail.com'       => 'mrfbuijxqyquqacf',
    'ediitoriaa@gmail.com'         => 'lkomlsisnvmpbovo',
    'eeventiss@gmail.com'          => 'gihivbpzajtqidau',
    'jtenzerr@gmail.com'           => 'ufctvlaewvpmrjjc',
    'farkhadovmalik@gmail.com'     => 'qfzdvjcosqrrojid',
];

$account = $argv[1] ?? null;
$batchFile = $argv[2] ?? null;

if (!$account || !isset($accounts[$account]) || !$batchFile) {
    die("Usage: php salvo.php <gmail_address> <batch_file>\n");
}

// Convert host path to container path if needed
$batchFile = str_replace('/Users/vaibhavchhimpa/AntiPalantir/salvo_data/', '/var/www/html/salvo_data/', $batchFile);

// ── Defines & Global Helpers ─────────────────────────────────────
define('SENT_FILE',   '/var/www/html/salvo_data/master_registry.txt');
define('LOG_FILE',    '/var/www/html/salvo_data/log.txt');
define('DAILY_FILE',  '/var/www/html/salvo_data/daily_' . date('Y-m-d') . '.json');
define('DAILY_MAX',   40); // GHOST OMEGA: 40 emails/day for survival
define('SLEEP_BASE',  1200); // 20 minutes base interval
define('SLEEP_JITTER', 240); // +/- 4 minutes randomization

function logit($msg) {
    file_put_contents(LOG_FILE, date('[Y-m-d H:i:s]') . " $msg\n", FILE_APPEND);
    echo date('[H:i:s]') . " $msg\n";
}

// ── Monday-Friday Check ─────────────────────────────────────────
$dow = date('N'); 
if ($dow > 5) {
    logit("[$account] Weekend detected — stopping safety protocol.");
    exit(0);
}

// ── Concurrency Lock ──────────────────────────────────────────
$lockFile = "/tmp/salvo_" . str_replace(['@', '.'], '_', $account) . ".lock";
if (file_exists($lockFile)) {
    $pid = trim(file_get_contents($lockFile));
    if ($pid && posix_getpgid((int)$pid)) {
        logit("[$account] ALREADY RUNNING (PID $pid) — skipping to prevent collision.");
        exit(0);
    }
}
file_put_contents($lockFile, getmypid());

// Register shutdown to clear lock
register_shutdown_function(function() use ($lockFile) {
    if (file_exists($lockFile)) unlink($lockFile);
});

// ── Daily Limit Check ──────────────────────────────────────────
$daily      = file_exists(DAILY_FILE) ? json_decode(file_get_contents(DAILY_FILE), true) : [];
$todayCount = $daily[$account] ?? 0;

if ($todayCount >= DAILY_MAX) {
    logit("[$account] Daily limit reached ($todayCount/" . DAILY_MAX . ") — skipping");
    exit(0);
}

// Read batch
if (!file_exists($batchFile)) {
    logit("[$account] ERROR: Batch file not found at $batchFile");
    exit(1);
}

$handle = fopen($batchFile, 'r');
$batch = [];
while (($row = fgetcsv($handle)) !== false) {
    if (count($row) < 4) continue;
    $email = strtolower(trim($row[3]));
    $rawName = trim($row[0]);
    $name = "";
    if ($rawName && strlen($rawName) > 1 && !preg_match('/^[A-Z] [A-Z]$/', $rawName)) {
        $name = explode(' ', $rawName)[0];
    }
    $batch[] = ['email' => $email, 'name' => $name];
}
fclose($handle);

if (empty($batch)) {
    logit("[$account] Batch is empty — finishing.");
    exit(0);
}

logit("[$account] STARTING BATCH: " . count($batch) . " leads (Today total: $todayCount)");

// ── Content ──────────────────────────────────────────────────────
$subjects = [
    'Epstein Alive : 2+2 logic and secret exit plan',
    'Epstein Alive : the 1.5-in-10-billion audit',
    'Epstein Alive : forensic proof of ghosting',
    'Epstein Alive : biometric mismatch on the gurney',
    'Epstein Alive : the MCC site scrub exposed',
    'Epstein Alive : why physics contradicts the news',
    'Epstein Alive : forensic markers don\'t lie',
    'Epstein Alive : the paper reality protocol',
    'Epstein Alive : shadow environment extraction',
    'Epstein Alive : the 2+2 math is finally here',
    'Epstein Alive : Audit reveals forensic ghosting',
    'Epstein Alive : The 1.5-in-10-billion forensic anomaly',
    'Epstein Alive : MCC footage script leaked',
    'Epstein Alive : Biometric mismatches confirm extraction',
    'Epstein Alive : Physics vs Official Narrative',
    'Epstein Alive : Genetic marker audit available',
    'Epstein Alive : Protocol shadow site operation',
    'Epstein Alive : PaperClip 2.0 implementation',
    'Epstein Alive : Biometric tragus mismatch found',
    'Epstein Alive : Mechanical signature of force',
];

$article = "https://burnthelies.com/posts/epstein-and-physics-the-2-plus-2-logic";

$bodies = [
    "{{hi}}\n\nOur goal is simple: awareness. I have been looking at the forensic data from the MCC event. When you factor in the simultaneous failure of surveillance, guard protocols, and the forensic markers, the math shows a 1.5-in-10-billion probability that the official story is true.\n\nRead article : $article\n\nOr Visit BurnTheLies organisation on BurnTheLies.com",
    "{{hi}}\n\nOur mission is awareness. The official narrative regarding the MCC event is a statistical anomaly so profound that it defies the fundamental laws of probability. Once you strip away the noise, you are left with a single figure: 1.5 in 10 billion.\n\nRead article : $article\n\nOr Visit BurnTheLies organisation on BurnTheLies.com",
    "{{hi}}\n\nThis is about awareness. Power does not destroy its most valuable nodes; it relocates them. To understand the MCC event, we must look at the Ghosting Doctrine — a refined version of Operation Paperclip, where assets are declared dead on paper to shield them while maintaining utility.\n\nRead article : $article\n\nOr Visit BurnTheLies organisation on BurnTheLies.com",
    "{{hi}}\n\nSpreading awareness: Ear morphology is as unique as a fingerprint. Comparative audits of the gurney photographs against archival photos reveal critical biological discrepancies in the tragus and helix structures.\n\nRead article : $article\n\nOr Visit BurnTheLies organisation on BurnTheLies.com",
    "{{hi}}\n\nOur only goal is awareness. Physics dictates that pressure equals force divided by area. To shatter rigid structures like the hyoid and thyroid cartilage, you require concentrated, active force or a high-velocity gravitational drop. The MCC cells lacked the clearance for such a drop.\n\nRead article : $article\n\nOr Visit BurnTheLies organisation on BurnTheLies.com",
];

// ── Connect SMTP ─────────────────────────────────────────────────
try {
    $transport = new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls');
    $transport->setUsername($account);
    $transport->setPassword($accounts[$account]);
    $transport->setStreamOptions(['ssl' => ['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false]]);
    $transport->setTimeout(60); 
    $mailer = new Swift_Mailer($transport);
} catch (Exception $e) {
    logit("[$account] SMTP CONNECT FAILED: " . $e->getMessage());
    exit(1);
}

// ── Fire ─────────────────────────────────────────────────────────
$sentCount = 0;
$senderName = ucfirst(explode('.', explode('@', $account)[0])[0]);

foreach ($batch as $i => $lead) {
    // Final check for global daily limit before each send
    if (($todayCount + $sentCount) >= DAILY_MAX) {
        logit("[$account] Hit DAILY_MAX mid-batch. Stopping.");
        break;
    }

    $email   = $lead['email'];
    $hi      = $lead['name'] ? "Hey " . $lead['name'] . "," : "Hey,";
    $subject = $subjects[array_rand($subjects)];
    $body    = str_replace('{{hi}}', $hi, $bodies[array_rand($bodies)]);

    try {
        $msg = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom([$account => $senderName])
            ->setTo([$email])
            ->setBody($body, 'text/plain');
        $mailer->send($msg);
        
        // Log immediately to Master Registry to prevent duplicates
        file_put_contents(SENT_FILE, $email . "\n", FILE_APPEND);
        
        $sentCount++;
        logit("[$account] [{$sentCount}/" . count($batch) . "] → $email");

        // Update daily count
        $daily[$account] = $todayCount + $sentCount;
        file_put_contents(DAILY_FILE, json_encode($daily, JSON_PRETTY_PRINT));

    } catch (Exception $e) {
        logit("[$account] FAIL → $email: " . $e->getMessage());
    }

    if ($i < count($batch) - 1) {
        $sec = SLEEP_BASE + rand(-SLEEP_JITTER, SLEEP_JITTER);
        sleep($sec);
    }
}

logit("[$account] BATCH COMPLETE. Total today: " . ($todayCount + $sentCount));
