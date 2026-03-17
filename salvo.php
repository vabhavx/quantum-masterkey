<?php
// ═══════════════════════════════════════════════════════
// OPERATION GRAPESHOT SALVO — SINGLE ACCOUNT SENDER
// Usage: php salvo.php <gmail_address>
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
if (!$account || !isset($accounts[$account])) {
    die("Usage: php salvo.php <gmail_address>\n");
}

// ── Monday-Friday Check ─────────────────────────────────────────
$dow = date('N'); // 1 (Mon) to 7 (Sun)
if ($dow > 5) {
    logit("[$account] Weekend detected — stopping per safety protocol.");
    exit(0);
}

define('LEADS_FILE',  '/var/www/html/salvo_data/leads.csv');
define('SENT_FILE',   '/var/www/html/salvo_data/sent.txt');
define('LOG_FILE',    '/var/www/html/salvo_data/log.txt');
define('DAILY_FILE',  '/var/www/html/salvo_data/daily_' . date('Y-m-d') . '.json');
define('PER_SLOT',    75);
define('DAILY_MAX',   300);
define('SLEEP_SEC',   36);

function logit($msg) {
    file_put_contents(LOG_FILE, date('[Y-m-d H:i:s]') . " $msg\n", FILE_APPEND);
    echo date('[H:i:s]') . " $msg\n";
}

// Load sent emails into a fast lookup map
$sent = [];
if (file_exists(SENT_FILE)) {
    foreach (file(SENT_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $sent[trim($line)] = true;
    }
}

// Check daily limit for this account
$daily      = file_exists(DAILY_FILE) ? json_decode(file_get_contents(DAILY_FILE), true) : [];
$todayCount = $daily[$account] ?? 0;
$remaining  = DAILY_MAX - $todayCount;

if ($remaining <= 0) {
    logit("[$account] Daily limit reached ($todayCount/" . DAILY_MAX . ") — skipping");
    exit(0);
}

$toSend = min(PER_SLOT, $remaining);

// Read leads and build batch of unsent emails
if (!file_exists(LEADS_FILE)) die("ERROR: No leads file at " . LEADS_FILE . "\n");

$handle  = fopen(LEADS_FILE, 'r');

// Detect columns (Manual override for specific CSV format)
$emailCol = 3;
$nameCol  = 0;

$batch = [];
while (($row = fgetcsv($handle)) !== false) {
    if (count($batch) >= $toSend) break;
    $email = strtolower(trim($row[$emailCol] ?? ''));
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
    if (isset($sent[$email])) continue;
    
    $rawName = trim($row[$nameCol] ?? '');
    // Clean up name: "S W" or ""
    $name = "";
    if ($rawName && strlen($rawName) > 1 && !preg_match('/^[A-Z] [A-Z]$/', $rawName)) {
        $name = explode(' ', $rawName)[0];
    }
    
    $batch[] = ['email' => $email, 'name' => $name];
    $sent[$email] = true; // mark immediately to prevent cross-process dupes
}
fclose($handle);

if (empty($batch)) {
    logit("[$account] No unsent leads remaining");
    exit(0);
}

logit("[$account] Starting — {$toSend} emails this slot (today: {$todayCount}/" . DAILY_MAX . ")");

// ── Subject rotation pool (50 variations) ────────────────────────
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
    'Epstein Alive : forensic data vs corporate media',
    'Epstein Alive : the statistical anomaly explained',
    'Epstein Alive : simultaneous failure probability',
    'Epstein Alive : the MCC DVR blackout investigation',
    'Epstein Alive : guard protocol collapse audit',
    'Epstein Alive : why the hyoid bone fractures matter',
    'Epstein Alive : biomechanics of the MCC event',
    'Epstein Alive : ghosting doctrine in action',
    'Epstein Alive : Operation Paperclip refined',
    'Epstein Alive : the deep storage transition',
    'Epstein Alive : genetic mismatch findings',
    'Epstein Alive : tragus and helix discrepancy',
    'Epstein Alive : skeletal trauma evidence',
    'Epstein Alive : thyroid cartilage fractures',
    'Epstein Alive : the 10th grade math test',
    'Epstein Alive : probability of the official story',
    'Epstein Alive : forensics of the ligature mark',
    'Epstein Alive : missing cellmate protocol failure',
    'Epstein Alive : the forensic signature of force',
    'Epstein Alive : optical silence in the MCC',
    'Epstein Alive : how the system relocates nodes',
    'Epstein Alive : the trillion-dollar intelligence fakes',
    'Epstein Alive : historical precedent of faked deaths',
    'Epstein Alive : Arkady Babchenko vs Epstein',
    'Epstein Alive : the site scrub conclusion',
    'Epstein Alive : legally dead physically relocated',
    'Epstein Alive : audit of the gurney photos',
    'Epstein Alive : why 2+2 equals 4 in this case',
    'Epstein Alive : decoding the forensic record',
    'Epstein Alive : the MCC blackout targeted unit',
    'Epstein Alive : forensic literature vs autopsy',
    'Epstein Alive : the patterns of bilateral fracture',
    'Epstein Alive : force vs gravitational drop math',
    'Epstein Alive : internal memo on ghosting',
    'Epstein Alive : the human server extraction',
    'Epstein Alive : why no dead man switch triggered',
    'Epstein Alive : the biometric record audit',
    'Epstein Alive : forensic markers of homicidal force',
    'Epstein Alive : the truth behind the paper reality',
    'Epstein Alive : final site scrub at the MCC',
];

// ── Body template pool (5 variations) ───────────────────────────
$bodies = [
    "{{hi}}\n\nI have been looking at the forensic data from the MCC event. When you factor in the simultaneous failure of surveillance, guard protocols, and the forensic markers, the math shows a 1.5-in-10-billion probability that the official story is true.\n\nBurnTheLies has released the full 2+2 audit showing how physics contradicts the narrative. Read the breakdown here: https://burn-odq3ad09v-vabhavxs-projects.vercel.app/posts/epstein-and-physics-the-2-plus-2-logic\n\n- The BurnTheLies Team",
    "{{hi}}\n\nThe official narrative regarding the MCC event is a statistical anomaly so profound that it defies the fundamental laws of probability. Once you strip away the noise, you are left with a single figure: 1.5 in 10 billion.\n\nMathematics taught in 10th grade creates a conclusion as easy as 2+2. The forensic audit is live:\nhttps://burn-odq3ad09v-vabhavxs-projects.vercel.app/posts/epstein-and-physics-the-2-plus-2-logic",
    "{{hi}}\n\nPower does not destroy its most valuable nodes; it relocates them. To understand the MCC event, we must look at the Ghosting Doctrine — a refined version of Operation Paperclip, where assets are declared dead on paper to shield them while maintaining utility.\n\nThe 2+2 logic behind the extraction is now public:\nhttps://burn-odq3ad09v-vabhavxs-projects.vercel.app/posts/epstein-and-physics-the-2-plus-2-logic",
    "{{hi}}\n\nEar morphology is as unique as a fingerprint. Comparative audits of the gurney photographs against archival photos reveal critical biological discrepancies in the tragus and helix structures.\n\nThe subject on the gurney displays a genetically distinct ear morphology. See the biometric proof for yourself:\nhttps://burn-odq3ad09v-vabhavxs-projects.vercel.app/posts/epstein-and-physics-the-2-plus-2-logic",
    "{{hi}}\n\nPhysics dictates that pressure equals force divided by area. To shatter rigid structures like the hyoid and thyroid cartilage, you require concentrated, active force or a high-velocity gravitational drop. The MCC cells lacked the clearance for such a drop.\n\nThe mechanical signature suggests manual force while the cameras were dark. Full breakdown:\nhttps://burn-odq3ad09v-vabhavxs-projects.vercel.app/posts/epstein-and-physics-the-2-plus-2-logic",
];

// ── Connect SMTP ─────────────────────────────────────────────────
try {
    $transport = new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls');
    $transport->setUsername($account);
    $transport->setPassword($accounts[$account]);
    $mailer = new Swift_Mailer($transport);
} catch (Exception $e) {
    logit("[$account] SMTP CONNECT FAILED: " . $e->getMessage());
    exit(1);
}

// ── Fire ─────────────────────────────────────────────────────────
$sentCount  = 0;
$sentEmails = [];
$senderName = ucfirst(explode('.', explode('@', $account)[0])[0]);

foreach ($batch as $i => $lead) {
    $email    = $lead['email'];
    $name     = $lead['name'];
    $hi       = $name ? "Hey $name," : "Hey,";
    $subject  = $subjects[array_rand($subjects)];
    $body     = str_replace('{{hi}}', $hi, $bodies[array_rand($bodies)]);

    try {
        $msg = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom([$account => $senderName])
            ->setTo([$email])
            ->setBody($body, 'text/plain');
        $mailer->send($msg);
        $sentEmails[] = $email;
        $sentCount++;
        logit("[$account] [{$sentCount}/{$toSend}] → $email");
    } catch (Exception $e) {
        logit("[$account] FAIL → $email: " . $e->getMessage());
    }

    if ($i < count($batch) - 1) sleep(SLEEP_SEC);
}

// ── Persist state ─────────────────────────────────────────────────
if (!empty($sentEmails)) {
    file_put_contents(SENT_FILE, implode("\n", $sentEmails) . "\n", FILE_APPEND);
}
$daily[$account] = $todayCount + $sentCount;
file_put_contents(DAILY_FILE, json_encode($daily, JSON_PRETTY_PRINT));

logit("[$account] COMPLETE — $sentCount sent today total: " . $daily[$account] . "/" . DAILY_MAX);
