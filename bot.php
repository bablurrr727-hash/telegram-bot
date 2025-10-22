<?php
// Get bot token from environment variables
$botToken = getenv('BOT_TOKEN');
$website  = "https://api.telegram.org/bot".$botToken;

// Menu and subscription plans
$menu = "All Available Admin Panels ✅
1️⃣ RTO CHALAN APP ✅
2️⃣ PM-Kisan APP ✅
3️⃣ PM AAWAS YOJANA APP ✅
4️⃣ Customer Support APP ✅
5️⃣ Health Insurance APP ✅
6️⃣ Electricity Bill APP ✅
7️⃣ Delhi Jal Board APP ✅
8️⃣ All Bank NetBanking APP ✅
9️⃣ All Bank Credit Card APP ✅
🔟 Other APP ✅

Reply with the number of the App you want 👇";

$plans = "💰 *Subscription Plans:* 

| Plan | Price | APKs | Valid |
|------|:------:|:----:|:------:|
| M1 |   $79 | 1 APK | 1 Month |
| M2 | $129 | 1 APK | 2 Months |
| M3 | $169 | 1 APK | 3 Months |

🪙 *Note:* Only USDT is accepted.";

// File to store user states
$stateFile = __DIR__ . "/user_states.json";
$userStates = file_exists($stateFile) ? json_decode(file_get_contents($stateFile), true) : [];

// Send message
function sendMessage($chatId, $text, $parse = "Markdown") {
    global $website;
    $url = $website."/sendMessage?chat_id=".$chatId."&text=".urlencode($text)."&parse_mode=".$parse;
    @file_get_contents($url);
}

// Send photo
function sendPhoto($chatId, $photoUrl, $caption = "") {
    global $website;
    $url = $website."/sendPhoto?chat_id=".$chatId."&photo=".urlencode($photoUrl)."&caption=".urlencode($caption);
    @file_get_contents($url);
}

// Read Telegram POST data
$update = file_get_contents("php://input");
$update = json_decode($update, true);

if (!isset($update['message'])) exit;

$chatId = $update['message']['chat']['id'];
$text   = $update['message']['text'] ?? "";

// Check user state
$state = $userStates[$chatId]['state'] ?? null;

// Handle commands
if ($text == "/start") {
    sendMessage($chatId, "👋 Welcome! I’m your App Selection Bot.\n\n".$menu);
    unset($userStates[$chatId]); // reset state
}
elseif ($state === "waiting_for_custom_input") {
    // User previously selected 8,9,10 and now sending custom app input
    $selectedApp = $userStates[$chatId]['selected_app'];
    sendMessage($chatId, "Thanks! We received your request for *App #$selectedApp*:\n\n\"$text\"\n\nWe will contact you shortly to confirm your order.");
    unset($userStates[$chatId]); // clear state
}
elseif (preg_match('/^(10|[1-9])$/', $text)) {
    if (in_array($text, ["8","9","10"])) {
        // Ask user to input details
        sendMessage($chatId, "You selected *App #$text* ✅\n\nPlease type the name or details of the app you want to request:");
        // Save state
        $userStates[$chatId] = [
            'state' => 'waiting_for_custom_input',
            'selected_app' => $text
        ];
    } else {
        // Show subscription plans and design options
        sendMessage($chatId, "You selected *App #$text* ✅\n\n".$plans);
        sendMessage($chatId, "Now please choose your design style:\n1️⃣ Modern\n2️⃣ Minimal\n3️⃣ Professional\n4️⃣ Gradient\nReply with design number 👇");
    }
}
elseif (in_array((string)$text, ["1","2","3","4"])) {
    $designs = [
        "1" => "https://i.imgur.com/jO1aN7k.png",
        "2" => "https://i.imgur.com/5m0Uj6T.png",
        "3" => "https://i.imgur.com/ViqSdZb.png",
        "4" => "https://i.imgur.com/pv3v4Sv.png"
    ];
    sendPhoto($chatId, $designs[$text], "Here’s your selected design style ✅\n\nNow proceed with payment to confirm your order:");
    sendPhoto($chatId, "https://i.imgur.com/J8VQz6D.png", "💳 Scan this to pay in USDT (mock example).");
}
else {
    sendMessage($chatId, "Please send /start to begin again.");
}

// Save updated states
file_put_contents($stateFile, json_encode($userStates, JSON_PRETTY_PRINT));
?>
