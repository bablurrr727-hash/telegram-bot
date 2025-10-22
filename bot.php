<?php
$botToken = "YOUR_BOT_TOKEN_HERE"; // ← replace with your real token
$website = "https://api.telegram.org/bot".$botToken;

$update = file_get_contents("php://input");
$update = json_decode($update, TRUE);

$chatId = $update["message"]["chat"]["id"];
$text = isset($update["message"]["text"]) ? trim($update["message"]["text"]) : "";

$menu = "All Available Admin Panels ✅
✍️✍️✍️
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
| M1 | $79 | 1 APK | 1 Month |
| M2 | $129 | 1 APK | 2 Months |
| M3 | $169 | 1 APK | 3 Months |

🪙 *Note:* Only USDT is accepted.
(If you use UPI, you need to purchase USDT first — e.g., on Binance.)";

function sendMessage($chatId, $text, $parse = "Markdown") {
    global $website;
    file_get_contents($website."/sendMessage?chat_id=".$chatId."&text=".urlencode($text)."&parse_mode=".$parse);
}

function sendPhoto($chatId, $photoUrl, $caption = "") {
    global $website;
    file_get_contents($website."/sendPhoto?chat_id=".$chatId."&photo=".urlencode($photoUrl)."&caption=".urlencode($caption));
}

if ($text == "/start") {
    sendMessage($chatId, "👋 Welcome! I’m your App Selection Bot.\n\n".$menu);
}
elseif (preg_match('/^[1-9]|10$/', $text)) {
    sendMessage($chatId, "You selected *App #$text* ✅\n\n".$plans);
    sendMessage($chatId, "Now please choose your design style:\n1️⃣ Modern\n2️⃣ Minimal\n3️⃣ Professional\n4️⃣ Gradient\nReply with design number 👇");
}
elseif (in_array($text, ["1", "2", "3", "4"])) {
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
?>
