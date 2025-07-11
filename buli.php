<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>📘 বুলি কোড টেস্টিং ও গাইড</title>
    <style>
        body { font-family: 'SolaimanLipi', sans-serif; background: #eef2f7; padding: 30px; }
        .doc, .interpreter { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 2px 2px 8px #ccc; margin-bottom: 30px; }
        textarea { width: 100%; height: 150px; font-size: 16px; margin-top: 10px; padding: 10px; }
        .output { background: #f6f9fc; padding: 15px; border-radius: 5px; margin-top: 20px; border: 1px solid #ccc; }
        button { background: #1a3d7c; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #163164; }
        h1, h2 { color: #1a3d7c; }
    </style>
</head>
<body>

    <div class="doc">
        <h1>📘 বুলি প্রোগ্রামিং ভাষার গাইড</h1>
        <p>বুলি হলো শিশুদের জন্য একটি বাংলায় লেখা প্রোগ্রামিং ভাষা। নিচে কিছু মূল শব্দ এবং সিনট্যাক্স:</p>
        <ul>
            <li><strong>লিখো "মেসেজ"</strong> – স্ক্রিনে বার্তা দেখায়</li>
            <li><strong>যদি (শর্ত)</strong> – শর্ত অনুযায়ী কাজ চালায়</li>
            <li><strong>না হলে</strong> – বিকল্প শর্ত</li>
            <li><strong>পর্যন্ত (শর্ত)</strong> – পুনরাবৃত্তি চালায়</li>
        </ul>
        <p>উদাহরণ:</p>
        <pre>
লিখো "হ্যালো শিশু!"
যদি (৫ > ৩) {
    লিখো "এটি সত্যি"
}
        </pre>
    </div>

    <div class="interpreter">
        <h2>🧪 বুলি কোড লিখো ও পরীক্ষাও করো</h2>
        <form method="post">
            <textarea name="code" placeholder='লিখো "আমি শেখছি!"'></textarea><br><br>
            <button type="submit">চালাও</button>
        </form>

        <div class="output">
            <h3>📤 আউটপুট:</h3>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $code = $_POST["code"];
                $lines = explode("\n", $code);
                $inLoop = false;
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (preg_match('/লিখো\s+"(.+?)"/u', $line, $matches)) {
                        echo "<div>" . htmlspecialchars($matches[1]) . "</div>";
                    } elseif (preg_match('/যদি\s*\((.+?)\)\s*\{/u', $line, $matches)) {
                        echo "<div style='color:green;'>👉 শর্ত পাওয়া গেছে: (" . htmlspecialchars($matches[1]) . ") [সিমুলেশন মোড]</div>";
                    } elseif (preg_match('/না হলে/u', $line)) {
                        echo "<div style='color:orange;'>🤔 বিকল্প শর্ত (না হলে)</div>";
                    } elseif (preg_match('/পর্যন্ত\s*\((.+?)\)/u', $line, $matches)) {
                        if ($inLoop) {
                            $inLoop = false;
                            echo "<div style='color:purple;'>🔄 লুপ বন্ধ (" . htmlspecialchars($matches[1]) . ") [সিমুলেশন মোড]</div>";
                        } else {
                            $inLoop = true;
                            echo "<div style='color:purple;'>🔄 লুপ শুরু (" . htmlspecialchars($matches[1]) . ") [সিমুলেশন মোড]</div>";
                        }
                    } else {
                        echo "<span style='color:gray;'>⚠️ অপরিচিত কমান্ড: </span> " . htmlspecialchars($line) . "<br>";
                    }
                }
            }
            ?>
        </div>
    </div>

</body>
</html>