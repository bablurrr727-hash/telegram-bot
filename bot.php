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
    "1" => "https://i.imgur.com/jO1aN7k.png",
    "2" => "https://i.imgur.com/5m0Uj6T.png",
    "3" => "https://i.imgur.com/ViqSdZb.png",
    "4" => "https://i.imgur.com/pv3v4Sv.png"
];

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
    sendMessage($chatId, "You selected plan: *$plan*\n\nNow choose your design style:");

    global $designs;
    foreach ($designs as $num => $url) {
        sendPhoto($chatId, $url, "Design #$num");
    }
    
    // Save plan selection
    global $userStates;
    $userStates[$chatId]['plan'] = $plan;
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
    $userStates[$chatId]['state'] = "waiting_for_plan";

    sendMessage($chatId, "Thanks! You requested App #".$userStates[$chatId]['selected_app'].":\n\"$text\"\n");
    sendPlanOptions($chatId);
}

// Waiting for design selection (after plan selected)
elseif ($state === "waiting_for_design_selection" && in_array($text, ["1","2","3","4"])) {
    $selectedApp = $userStates[$chatId]['selected_app'];
    $appDetails  = $userStates[$chatId]['app_details'] ?? "App #$selectedApp";
    $plan        = $userStates[$chatId]['plan'] ?? "";

    sendPhoto($chatId, $designs[$text], "Here’s your selected design style ✅\n\nApp: *$appDetails*\nPlan: *$plan*\n\nNow proceed with payment to confirm your order:");
    sendPhoto($chatId, "https://i.imgur.com/J8VQz6D.png", "💳 Scan this to pay in USDT (mock example).");

    unset($userStates[$chatId]);
}

// Waiting for plan selection (after app 1-7 or custom input)
elseif ($state === "waiting_for_plan") {
    sendMessage($chatId, "Please select a plan by tapping one of the buttons above.");
}

// User selects an app number
elseif (preg_match('/^(10|[1-9])$/', $text)) {
    if (in_array($text, ["8","9","10"])) {
        sendMessage($chatId, "You selected *App #$text* ✅\n\nPlease type the name or details of the app you want to request:");
        $userStates[$chatId] = [
            'state' => 'waiting_for_custom_input',
            'selected_app' => $text
        ];
    } else {
        // Apps 1-7: save app, show plans
        $userStates[$chatId] = [
            'state' => 'waiting_for_plan',
            'selected_app' => $text
        ];
        sendMessage($chatId, "You selected *App #$text* ✅\n");
        sendPlanOptions($chatId);
    }
}

// Waiting for design number (after plan selection)
elseif (in_array($text, ["1","2","3","4"])) {
    sendMessage($chatId, "Please select a plan first using the buttons above.");
}

else {
    sendMessage($chatId, "Please send /start to begin again.");
}

file_put_contents($stateFile, json_encode($userStates, JSON_PRETTY_PRINT));
?>
