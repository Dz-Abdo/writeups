<?php
require_once __DIR__ . '/public/includes/db.php';

$db = get_db();
$db->exec(file_get_contents(__DIR__ . '/schema.sql'));

$users = [
    ['marina_deep',    'marina@example.com',  'Marina Voss',   'PADI Divemaster based in the Maldives. Obsessed with mantas and wide-angle photography.',           'Malé, Maldives',              'Divemaster',       312],
    ['reef_hunter',    'carlos@example.com',  'Carlos Ruiz',   'Technical diver and underwater naturalist. Cave certified. Yucatán cenotes are my cathedral.',       'Playa del Carmen, Mexico',    'TDI Tech Diver',   540],
    ['blue_iris',      'yuki@example.com',    'Yuki Tanaka',   'Macro photographer chasing nudibranchs and the weird stuff nobody notices. Based in Anilao.',         'Anilao, Philippines',         'PADI Advanced OW', 180],
    ['tidal_flux',     'sam@example.com',     'Sam Okafor',    'Wreck diver and maritime historian. If it sank before 1970, I want to see it.',                       'Cape Town, South Africa',     'NAUI Divemaster',  275],
    ['current_rider',  'petra@example.com',   'Petra Novák',   'Freediver and spearfisher. I hold my breath for a living. Based on the Dalmatian coast.',            'Split, Croatia',              'AIDA 3 Freediver',  95],
];

$uStmt = $db->prepare('INSERT OR IGNORE INTO users (username,email,password_hash,display_name,bio,location,cert_level,dive_count) VALUES (?,?,?,?,?,?,?,?)');
foreach ($users as $u) {
    $uStmt->bindValue(1, $u[0]);
    $uStmt->bindValue(2, $u[1]);
    $uStmt->bindValue(3, password_hash('password123', PASSWORD_BCRYPT));
    $uStmt->bindValue(4, $u[2]);
    $uStmt->bindValue(5, $u[3]);
    $uStmt->bindValue(6, $u[4]);
    $uStmt->bindValue(7, $u[5]);
    $uStmt->bindValue(8, $u[6], SQLITE3_INTEGER);
    $uStmt->execute();
}

$uid = [];
$r = $db->query('SELECT id,username FROM users');
while ($row = $r->fetchArray(SQLITE3_ASSOC)) $uid[$row['username']] = $row['id'];

$logs = [
    [$uid['marina_deep'],   'Manta Night at Lanai Point',
     "Dropped in just after sunset with a full moon barely visible through 18 metres of water. The mantas came in slow at first — one, then three, then a whole squadron barrel-rolling through the torch beams. I've done this dive forty times and it never gets old. The plankton concentration was off the charts tonight, which probably explains the turnout. Stayed down 62 minutes until my deco obligation made me surface. Came up grinning into the dark.",
     'North Malé Atoll, Maldives', 18.0, 62, 'Excellent (20m+)'],

    [$uid['marina_deep'],   'Hammerheads at Rasdhoo',
     "Early start — in the water by 05:45 to catch the hammerheads before the light pushes them deep. Descended fast to 30 m and waited on the plateau. About twelve minutes in, the first school materialised out of the blue — twenty or so scalloped hammerheads in loose formation. They were completely unbothered by us. Followed them until my NDL hit 5 min, then a slow ascent. Surface temp 29 °C but a cool 25 °C at depth.",
     'Rasdhoo Atoll, Maldives', 30.5, 48, 'Good (15m)'],

    [$uid['reef_hunter'],   'Dos Ojos — The Barbie Line',
     "The Barbie Line section of Dos Ojos is everything people say it is. The halocline shimmer makes the water look broken, like someone poured oil over glass. Visibility in the freshwater layer was absolute — you could count every stalactite on the ceiling from 20 metres away. We ran our guideline in and turned at 45 minutes on gas. The light shaft at the main cavern entrance on the way out was almost too beautiful to photograph; every shot looks fake.",
     'Tulum, Quintana Roo, Mexico', 12.0, 90, 'Exceptional (30m+)'],

    [$uid['reef_hunter'],   'Punta Sur Deep Wall, Cozumel',
     "Drift diving the south wall in a decent current — dropped to 38 m where the wall goes vertical and just flew along it. Huge black corals down deep, some ancient-looking sea fans the size of dining tables. A nurse shark tucked under a ledge at about 32 m, completely unbothered. The upcurrent near the point gave us a free elevator to 5 m for the safety stop. Fifty-five minutes and I barely kicked.",
     'Cozumel, Mexico', 38.0, 55, 'Very Good (18m)'],

    [$uid['blue_iris'],     "Nudi Heaven at Kirby's Rock",
     "Four new species in one dive. FOUR. A Chromodoris willani I'd never seen in person, two Flabellina iodinea doing their thing on a hydroid, a tiny Tambja morosa I almost finned over, and — the highlight — a Gymnodoris ceylonica hunting another nudibranch in real time. I was so absorbed I surfaced with 20 bar. The muck here is genuinely world-class if you slow down and look at the right scale.",
     'Anilao, Batangas, Philippines', 8.5, 68, 'Moderate (8m)'],

    [$uid['blue_iris'],     'Frogfish Hunt at Twin Rocks',
     "Spent the whole dive looking for the painted frogfish that's been reported on the rubble slope. Found it after 35 minutes — sitting completely motionless on a sponge that matched it perfectly. Bright orange, maybe 8 cm. Tried every angle but the best shot required lying flat on the sand and waiting for it to open its mouth. It obliged. Got one sharp frame I'm genuinely proud of.",
     'Anilao, Batangas, Philippines', 14.0, 72, 'Good (12m)'],

    [$uid['tidal_flux'],    'SS Thistlegorm — Holds 2 & 3',
     "The Thistlegorm lives up to the legend. Dropped through the thermocline at 15 m and the wreck emerged below like a floating city. Hold 2 still has the motorcycles and trucks exactly as they settled in 1941. The ammunition hold is off limits but you can peer in — the shells are stacked floor to ceiling. A huge grouper has taken up residence near the stern gun. Current was manageable. Night dive tomorrow if conditions hold.",
     'Red Sea, Egypt', 30.0, 50, 'Good (15m)'],

    [$uid['tidal_flux'],    'HMHS Britannic — Bell Deck',
     "The Britannic is everything the Titanic should be: accessible, spectacular, and sitting upright in 120 m of Greek water. We did a trimix bounce to the bell deck at 90 m. The portholes are still intact along the promenade. One of the giant lifeboat davits is bent outward from when she went down — lifeboats launched while she was still moving tore them apart. History you can touch. Six-hour surface interval before the second dive.",
     'Aegean Sea, Greece', 90.0, 25, 'Exceptional (40m+)'],

    [$uid['current_rider'], 'Blue Cave Freedive, Biševo',
     "On breath-hold to the Blue Cave on a flat-calm morning — the light comes in from below the entrance and turns everything inside electric blue and silver. I did seven dives in the cave, the longest a 2:40 hold. The colour is completely unreal and impossible to photograph faithfully. A few snorkellers came in on the boat tour and seemed baffled that I was there without scuba. Freediving this spot belongs on every breath-hold diver's list.",
     'Biševo Island, Croatia', 15.0, 0, 'Exceptional (30m+)'],

    [$uid['current_rider'], 'Spearfishing the Dalmatian Shelf',
     "Long surface swim to the shelf edge, then working the 18–25 m range hunting amberjack. Water clear enough to see bottom from the surface. Three amberjack in two hours — released two, kept one well over legal size. A large dusky grouper followed me for most of the session, hoping for scraps. The physical effort of freedive spearfishing over four hours is real — I was properly tired by the swim home.",
     'Vis Island, Croatia', 25.0, 0, 'Very Good (20m)'],
];

$lStmt = $db->prepare('INSERT OR IGNORE INTO dive_logs (user_id,title,body,location,depth_m,duration_min,visibility) VALUES (?,?,?,?,?,?,?)');
foreach ($logs as $l) {
    $lStmt->bindValue(1, $l[0], SQLITE3_INTEGER);
    $lStmt->bindValue(2, $l[1]);
    $lStmt->bindValue(3, $l[2]);
    $lStmt->bindValue(4, $l[3]);
    $lStmt->bindValue(5, $l[4], SQLITE3_FLOAT);
    $lStmt->bindValue(6, $l[5], SQLITE3_INTEGER);
    $lStmt->bindValue(7, $l[6]);
    $lStmt->execute();
}

$lid = [];
$r = $db->query('SELECT id FROM dive_logs ORDER BY id');
$i = 1;
while ($row = $r->fetchArray(SQLITE3_ASSOC)) $lid[$i++] = $row['id'];

$likes = [
    [$uid['reef_hunter'],   $lid[1]],  [$uid['blue_iris'],    $lid[1]],
    [$uid['tidal_flux'],    $lid[1]],  [$uid['current_rider'],$lid[1]],
    [$uid['marina_deep'],   $lid[3]],  [$uid['blue_iris'],    $lid[3]],
    [$uid['tidal_flux'],    $lid[3]],  [$uid['marina_deep'],  $lid[5]],
    [$uid['reef_hunter'],   $lid[5]],  [$uid['tidal_flux'],   $lid[5]],
    [$uid['marina_deep'],   $lid[7]],  [$uid['reef_hunter'],  $lid[7]],
    [$uid['blue_iris'],     $lid[7]],  [$uid['current_rider'],$lid[7]],
    [$uid['marina_deep'],   $lid[9]],  [$uid['reef_hunter'],  $lid[9]],
    [$uid['blue_iris'],     $lid[2]],  [$uid['current_rider'],$lid[4]],
    [$uid['marina_deep'],   $lid[6]],  [$uid['tidal_flux'],   $lid[8]],
    [$uid['current_rider'], $lid[2]],  [$uid['tidal_flux'],   $lid[6]],
];

$likeStmt = $db->prepare('INSERT OR IGNORE INTO likes (user_id,log_id) VALUES (?,?)');
foreach ($likes as $p) {
    $likeStmt->bindValue(1, $p[0], SQLITE3_INTEGER);
    $likeStmt->bindValue(2, $p[1], SQLITE3_INTEGER);
    $likeStmt->execute();
}

$follows = [
    [$uid['reef_hunter'],   $uid['marina_deep']],
    [$uid['blue_iris'],     $uid['marina_deep']],
    [$uid['tidal_flux'],    $uid['marina_deep']],
    [$uid['current_rider'], $uid['marina_deep']],
    [$uid['marina_deep'],   $uid['reef_hunter']],
    [$uid['blue_iris'],     $uid['reef_hunter']],
    [$uid['tidal_flux'],    $uid['reef_hunter']],
    [$uid['marina_deep'],   $uid['blue_iris']],
    [$uid['reef_hunter'],   $uid['blue_iris']],
    [$uid['current_rider'], $uid['blue_iris']],
    [$uid['marina_deep'],   $uid['tidal_flux']],
    [$uid['blue_iris'],     $uid['tidal_flux']],
    [$uid['marina_deep'],   $uid['current_rider']],
    [$uid['reef_hunter'],   $uid['current_rider']],
];

$fStmt = $db->prepare('INSERT OR IGNORE INTO follows (follower_id,followee_id) VALUES (?,?)');
foreach ($follows as $p) {
    $fStmt->bindValue(1, $p[0], SQLITE3_INTEGER);
    $fStmt->bindValue(2, $p[1], SQLITE3_INTEGER);
    $fStmt->execute();
}

echo "Seeded OK.\n";
