<?php
$botToken = getenv('BOT_TOKEN');
$website  = "https://api.telegram.org/bot".$botToken;

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

$stateFile = __DIR__ . "/user_states.json";
$userStates = file_exists($stateFile) ? json_decode(file_get_contents($stateFile), true) : [];

$designs = [
    "1" => "https://github.com/bablurrr727-hash/telegram-bot/blob/main/WhatsApp%20Image%202025-09-07%20at%2017.49.48.jpeg",
    "2" => "https://i.imgur.com/5m0Uj6T.png",
    "3" => "https://github.com/bablurrr727-hash/telegram-bot/blob/main/photo_6226367695230715396_y.jpg",
    "4" => "https://github.com/bablurrr727-hash/telegram-bot/blob/main/photo_6226367695230715394_y.jpg"
];

$qrUSDT = "https://i.imgur.com/J8VQz6D.png"; // Example USDT QR

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

// Send plan options as inline buttons
function sendPlanOptions($chatId) {
    global $website;
    $inlineKeyboard = [
        "inline_keyboard" => [
            [
                ["text" => "M1 - $79 - 1 Month", "callback_data" => "plan_M1"],
                ["text" => "M2 - $129 - 2 Months", "callback_data" => "plan_M2"],
                ["text" => "M3 - $169 - 3 Months", "callback_data" => "plan_M3"]
            ]
        ]
    ];
    $data = [
        'chat_id' => $chatId,
        'text' => "💰 Choose your subscription plan:",
        'reply_markup' => json_encode($inlineKeyboard)
    ];
    $options = http_build_query($data);
    @file_get_contents($website."/sendMessage?$options");
}

// Read Telegram POST data
$update = file_get_contents("php://input");
$update = json_decode($update, true);

$chatId = $update['message']['chat']['id'] ?? null;
$text   = $update['message']['text'] ?? null;

// Handle callback queries (plan selection)
if (isset($update['callback_query'])) {
    $chatId = $update['callback_query']['message']['chat']['id'];
    $plan = $update['callback_query']['data']; // e.g., plan_M1
    global $userStates, $qrUSDT;

    $selectedApp = $userStates[$chatId]['selected_app'] ?? "App";
    $appDetails  = $userStates[$chatId]['app_details'] ?? $selectedApp;
    $selectedDesign = $userStates[$chatId]['selected_design'] ?? "";

    sendMessage($chatId, "✅ You selected plan: *$plan*\nApp: *$appDetails*\nDesign: *$selectedDesign*");

    // Show USDT QR
    sendPhoto($chatId, $qrUSDT, "💳 Scan this QR to pay in USDT");

    // Clear state
    unset($userStates[$chatId]);
    file_put_contents(__DIR__ . "/user_states.json", json_encode($userStates, JSON_PRETTY_PRINT));
    exit;
}

// If no chatId or text, exit
if (!$chatId || !$text) exit;

$state = $userStates[$chatId]['state'] ?? null;

// START
if ($text == "/start") {
    sendMessage($chatId, "👋 Welcome! I’m your App Selection Bot.\n\n".$menu);
    unset($userStates[$chatId]);
}

// Waiting for custom input (apps 8-10)
elseif ($state === "waiting_for_custom_input") {
    $userStates[$chatId]['app_details'] = $text;
    $userStates[$chatId]['state'] = "waiting_for_design";

    // Show design options
    sendMessage($chatId, "Thanks! You requested App #".$userStates[$chatId]['selected_app'].":\n\"$text\"\n\nNow choose your design style:");
    foreach ($designs as $num => $url) {
        sendPhoto($chatId, $url, "Design #$num");
    }
}

// Waiting for design selection
elseif ($state === "waiting_for_design" && in_array($text, ["1","2","3","4"])) {
    $selectedApp = $userStates[$chatId]['selected_app'];
    $appDetails  = $userStates[$chatId]['app_details'] ?? "App #$selectedApp";
    $userStates[$chatId]['selected_design'] = $text;
    $userStates[$chatId]['state'] = "waiting_for_plan";

    sendPhoto($chatId, $designs[$text], "You selected Design #$text ✅\n\nNow choose your subscription plan:");
    sendPlanOptions($chatId);
}

// User selects an app number
elseif (preg_match('/^(10|[1-9])$/', $text)) {
    if (in_array($text, ["8","9","10"])) {
        // Ask for custom input
        sendMessage($chatId, "You selected *App #$text* ✅\n\nPlease type the name or details of the app you want to request:");
        $userStates[$chatId] = [
            'state' => 'waiting_for_custom_input',
            'selected_app' => $text
        ];
    } else {
        // Apps 1-7: skip custom input, go to design
        $userStates[$chatId] = [
            'state' => 'waiting_for_design',
            'selected_app' => $text
        ];
        sendMessage($chatId, "You selected *App #$text* ✅\n\nNow choose your design style:");
        foreach ($designs as $num => $url) {
            sendPhoto($chatId, $url, "Design #$num");
        }
    }
}

else {
    sendMessage($chatId, "Please send /start to begin again.");
}

// Save user states
file_put_contents($stateFile, json_encode($userStates, JSON_PRETTY_PRINT));
?>

