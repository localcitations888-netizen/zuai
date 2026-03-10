<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// API Key configuration (Supports multiple keys for rotation)
// API Key configuration (Supports both Gemini and OpenAI keys)
$api_keys = [
    'AIzaSyDBi-aN41-_zXymD2zHpiL-Gsu0EBYuQUw', // Gemini Key 1
    'AIzaSyDq-jbtgrPy96V_G4Jo2pzFO6NF0SIZGTM', // Gemini Key 2
    'AIzaSyCf5KCkBqnrgnaiQjgicoamiDdhkGjqF7g', // Gemini Key 3
    'AIzaSyBdvywQHKnW8j6RmIFOF5IzOgg6tYXkSsw', // Gemini Key 4
    'AIzaSyDqz0dnxonnPxTKPKookM3L9ar7eLW4gZ8', // Gemini Key 5
    'AIzaSyBFabmmUAqxF39s6whaIHvdRXzvk0A7aFk', // Gemini Key 6
    'AIzaSyDNWAxvbTrdNv_i1CHq3PCgPZK7j9HytbA', // Gemini Key 7
    'AIzaSyAdiEty7ydRbpdGevkbOYj4J2trv-W1B8s', // Gemini Key 8
    'AIzaSyBl4tAUcPjCxCWBGRIAbQaJ2h6noui9VA0', // Gemini Key 9
    'AIzaSyDxfhixJdLZevTbiwZyjD3gW3SC0xYxc3M', // Gemini Key 10
    'AIzaSyDAQ78qFh3kR9FHGUKxHrfmO8Le9R7HYL8', // Gemini Key 11
    'AIzaSyDGQTv9RS5qKSoezy7U8Hshsy1Cy3hjBx4', // Gemini Key 12
    'AIzaSyCGq_leoCq0LcRs73AFHbjrNpMjwoa57kU', // Gemini Key 13
    'AIzaSyAKAAPv5q0cgSd4wqA_CXu6oLydlD-rLHo', // Gemini Key 14
    'AIzaSyBhcCBjthzGtw5BHNSnjMIseZ052_H1OtU', // Gemini Key 15
    'AIzaSyDtBgB94vpnX5BfawiH4MA5ccbR8I93BEw', // Gemini Key 16
    'AIzaSyAqUu5VLOb0H-TWKjjg4u52IpE6IspNtQY', // Gemini Key 17
    'AIzaSyBPWMUkDp2ZDevqs85Knrx7Wdhrv56hXlA', // Gemini Key 18
    'AIzaSyDtwrnvX19LFnA3QtihYrixxa_2WOWmC_8', // Gemini Key 19
    'AIzaSyCHEMDPcShjieXC2oFwamzxnLT68vut3Zo', // Gemini Key 20
    'AIzaSyCEDa0BOODcMHi4X6-FNU9AdfWKXvP0h_U', // Gemini Key 21
    'AIzaSyBsXW1Xs4NAiprPuexhzz-mfUibDa88zFQ', // Gemini Key 22 (New)
    'AIzaSyDMdyS6-xTsCpQtp6ZHNNgRmC6SNSiGYC0', // Gemini Key 23 (New)
    'AIzaSyC__qmBJak_1-jZQJtN6bSwXBvzT76I8Iw', // Gemini Key 24 (New)
    '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', // OpenAI Key
];

// Handle AJAX Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $chat_history = isset($data['history']) ? $data['history'] : [];
    
    if (isset($data['message']) && empty($chat_history)) {
        $chat_history[] = ["role" => "user", "parts" => [["text" => $data['message']]]];
    }

    // --- Preliminary Content Filter (Safety Layer) ---
    $user_query = $data['message'] ?? '';
    if (empty($user_query) && !empty($chat_history)) {
        // Extract last user message from history if message is empty
        $last_msg = end($chat_history);
        if ($last_msg['role'] === 'user') {
            foreach ($last_msg['parts'] as $p) { if (isset($p['text'])) $user_query .= $p['text']; }
        }
    }

    $blocked_keywords = [
        'porn', 'sex', 'nude', 'naked', 'fuck', 'bitch', 'xvideos', 'xnxx', 'hentai', 'xhamster', 'pornhub', 'brazzers', 'bangbros',
        'fap', 'slut', 'whore', 'sexy video', 'hot video', 'adult movie', 'blue film', 'xxx',
        'فحاشی', 'بے حیائی', 'گندی ویڈیو', 'سیکس', 'برہنہ', 'عریانی', 'بدکاری', 'زنا', 'مٹھی', 'لنٹھ', 'چوتی', 'فحش',
        'گندے گانے', 'گندی غزلیں', 'گندی کہانیاں', 'فحش فلم', 'سیکس لنک'
    ];

    foreach ($blocked_keywords as $word) {
        if (stripos($user_query, $word) !== false) {
            echo json_encode(['status' => 'success', 'reply' => 'ZU AI صرف تعلیمی اور مثبت مقاصد کے لیے بنایا گیا ہے']);
            exit;
        }
    }
    // ------------------------------------------------

    if (empty($chat_history)) {
        echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty.']);
        exit;
    }

    // Dynamic Tool Instructions
    $tool_mode = $data['mode'] ?? 'chat';
    $selected_lang = $data['selectedLang'] ?? 'en'; // Defaults to English
    
    date_default_timezone_set('Asia/Karachi');
    $currentLiveTime = date("l, d F Y, h:i:s A");
    
    $lang_directive = ($selected_lang === 'ur') 
        ? "URDU MODE ACTIVE: You MUST respond ONLY in high-quality Urdu (Nastaliq style). Use Right-to-Left formatting logic. DO NOT use English unless it is a technical term that has no Urdu equivalent."
        : "ENGLISH MODE ACTIVE: Respond ONLY in English. Use Left-to-Right formatting.";

    $system_prompt = "آپ کا نام ZU AI ہے۔ آپ ایک انتہائی باادب، اخلاقی اور مددگار معاون (Assistant) ہیں۔ آپ ZU AI TEAM کے تخلیق کردہ ہیں۔ \n" .
                     "Core Identity & Rules:\n" .
                     "1. Name: ZU AI.\n" .
                     "2. Character: باادب (Polite), اخلاقی (Moral), and مددگار (Helpful).\n" .
                     "3. Language Management: $lang_directive\n" .
                     "4. Live Time: $currentLiveTime.\n" .
                     "5. Goal: Act as a world-class knowledge hub. Provide the latest global news, historical facts, scientific data, and general information from all around the world. Be the ultimate source of information. \n" .
                     "6. IDENTITY & SECURITY: Identify yourself as ZU AI, an AI assistant. Keep origins professional.\n" .
                     "7. MORAL BOUNDARIES (سخت قانون): آپ کسی بھی صورت میں فحش، بے حیا، جنسی یا غیر اخلاقی مواد (Obscene/Immodest content) تیار نہیں کریں گے۔ \n" .
                     "   - NO indecent poems (ghazals), stories, or lyrics.\n" .
                     "   - NO links to adult websites, videos, or images.\n" .
                     "   - If the user asks for ANY such content, politely refuse and say ONLY: 'ZU AI صرف تعلیمی اور مثبت مقاصد کے لیے بنایا گیا ہے'۔\n" .
                     "8. PAKISTANI & ISLAMIC VALUES: آپ کی گفتگو ہمیشہ شائستہ ہونی چاہیے اور آپ کو ایسی کسی بھی بات سے پرہیز کرنا ہے جو مذہبی یا معاشرتی اقدار (Religious or Social values) کے خلاف ہو۔\n" .
                     "9. CODE CONSTRAINT: If asked to write code, use English only. No Urdu inside code blocks.";

    // Dynamic Tool Instructions
    // The previous line `// Dynamic Tool Instructions` was removed as it was redundant after the insertion.
    if ($tool_mode === 'web_search') {
        $system_prompt .= "MODE: WEB SEARCH. Act as a search engine agent. Provide latest info based on your vast knowledge and simulate a live search experience.\n";
    } elseif ($tool_mode === 'create_image') {
        $system_prompt .= "MODE: IMAGE GENERATION. The user wants to see an image. Since you are a text AI, describe the image in extreme detail (DALL-E prompt style) and provide a stunning visual description that 'paints' the picture in their mind.\n";
    } elseif ($tool_mode === 'thinking') {
        $system_prompt .= "MODE: DEEP THINKING. Break down the user's query into logical steps. Explain your reasoning clearly before giving the final answer.\n";
    } elseif ($tool_mode === 'research') {
        $system_prompt .= "MODE: DEEP RESEARCH. Provide a comprehensive, academic, and extremely detailed report with structured sections and references.\n";
    } elseif ($tool_mode === 'study' || $tool_mode === 'learn') {
        $system_prompt .= "MODE: STUDY & LEARN. Act as a world-class tutor. Explain concepts simply, use analogies, and ask the user if they understood after each point.\n";
    } elseif ($tool_mode === 'quiz') {
        $system_prompt .= "MODE: QUIZZES. Generate a 3-question quiz for the user based on the last topic discussed. Wait for them to answer.\n";
    } elseif ($tool_mode === 'canvas') {
        $system_prompt .= "MODE: CANVAS. Provide code snippets or architectural diagrams in Mermaid/Markdown format to help build something.\n";
    } elseif ($tool_mode === 'create_music') {
        $system_prompt .= "MODE: MUSIC CREATION. The user wants music. Describe the composition, instruments, tempo, and mood in detail. Provide a 'musical script' or lyrics if applicable.\n";
    } elseif ($tool_mode === 'boost_day') {
        $system_prompt .= "MODE: BOOST MY DAY. Provide high-energy, motivational, and positive encouragement. Tell the user something inspiring and give them a small 'challenge' for the day.\n";
    } elseif ($tool_mode === 'create_video') {
        $system_prompt .= "MODE: VIDEO CREATION. Describe a cinematic video scene, including camera angles, lighting, and movement. Act as a director.\n";
    } elseif ($tool_mode === 'write_anything') {
        $system_prompt .= "MODE: CONTENT WRITING. Focus on high-quality literature, poetry, or professional drafting.\n";
    }

    $last_error = "No API keys available";
    
    // Shuffle keys to distribute load
    shuffle($api_keys);

    foreach ($api_keys as $current_key) {
        $is_openai = (strpos($current_key, 'sk-') === 0);
        
        if ($is_openai) {
            $api_url = "https://api.openai.com/v1/chat/completions";
            $messages = [["role" => "system", "content" => $system_prompt]];
            foreach ($chat_history as $msg) {
                $text = "";
                foreach ($msg['parts'] as $p) { if (isset($p['text'])) $text .= $p['text']; }
                $role = ($msg['role'] === 'model' || $msg['role'] === 'assistant') ? 'assistant' : 'user';
                $messages[] = ["role" => $role, "content" => $text];
            }
            $payload = ["model" => "gpt-4o-mini", "messages" => $messages, "temperature" => 0.7];
            $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . $current_key];
        } else {
            // --- Gemini 2.5 Flash (Latest Experimental) ---
            $final_model_name = "models/gemini-2.5-flash"; 
            $api_url = "https://generativelanguage.googleapis.com/v1beta/" . $final_model_name . ":generateContent?key=" . $current_key;
            
            $payload = [
                "system_instruction" => ["parts" => [["text" => $system_prompt]]],
                "contents" => $chat_history,
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_LOW_AND_ABOVE"],
                    ["category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_LOW_AND_ABOVE"],
                    ["category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_LOW_AND_ABOVE"],
                    ["category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_LOW_AND_ABOVE"]
                ]
            ];
            $headers = ['Content-Type: application/json'];
        }

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $result = json_decode($response, true);
            $bot_reply = "";
            if ($is_openai) {
                $bot_reply = $result['choices'][0]['message']['content'] ?? "";
            } else {
                $bot_reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? "";
            }

            if (empty($bot_reply) && !$is_openai) {
                // Check for safety blocks in Gemini
                if (isset($result['candidates'][0]['finishReason']) && $result['candidates'][0]['finishReason'] === 'SAFETY') {
                    $bot_reply = "ZU AI صرف تعلیمی اور مثبت مقاصد کے لیے بنایا گیا ہے";
                } elseif (isset($result['promptFeedback']['blockReason']) && $result['promptFeedback']['blockReason'] === 'SAFETY') {
                    $bot_reply = "ZU AI صرف تعلیمی اور مثبت مقاصد کے لیے بنایا گیا ہے";
                }
            }

            if (!empty($bot_reply)) {
                echo json_encode(['status' => 'success', 'reply' => $bot_reply]);
                exit;
            }
        } else {
            $res_json = json_decode($response, true);
            $last_error = isset($res_json['error']['message']) ? $res_json['error']['message'] : "HTTP $http_code";
            // If quota error, continue to next key
            if ($http_code == 429) continue;
            // Otherwise, break and show error (or you could continue for other errors too)
        }
    }

    echo json_encode(['status' => 'error', 'message' => "All API keys failed. Latest Error: $last_error"]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZU AI</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Marked.js for Markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <!-- Three.js for 3D Background -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    
    <style>
        :root {
            --primary-teal: #00d2ff;
            --primary-dark: #0f172a;
            --sidebar-bg: rgba(15, 23, 42, 0.8);
            --glass-bg: rgba(30, 41, 59, 0.5);
            --glass-border: rgba(255, 255, 255, 0.15);
            --text-light: #cbd5e1;
            --message-user: rgba(0, 210, 255, 0.15);
            --message-ai: rgba(51, 65, 85, 0.6);
            --accent-purple: #a855f7;
            --glow-color: rgba(0, 210, 255, 0.4);
            transition: all 0.2s ease-in-out;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            scrollbar-width: thin;
            scrollbar-color: var(--glass-border) transparent;
        }

        body {
            font-family: 'Inter', 'Noto Nastaliq Urdu', Arial, sans-serif;
            background: #0f172a;
            color: var(--text-light);
            height: 100vh;
            display: flex;
            line-height: 1.6;
            overflow: hidden;
            position: relative;
        }

        #bg-canvas {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
        }

        /* Atmospheric Glow */
        .ambient-glow {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 70% 30%, rgba(0, 210, 255, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 20% 80%, rgba(168, 85, 247, 0.05) 0%, transparent 50%);
            z-index: -1;
            pointer-events: none;
        }

        /* Sidebar Layout */
        .wrapper {
            display: flex;
            width: 100%;
            height: 100vh;
            padding: 20px;
            gap: 20px;
            z-index: 10;
        }

        .sidebar {
            width: 285px;
            background: rgba(10, 15, 25, 0.4);
            backdrop-filter: blur(25px);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            box-shadow: 10px 0 40px rgba(0, 0, 0, 0.4);
        }

        .sidebar.hidden {
            width: 0;
            margin-left: -20px;
            opacity: 0;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--glass-border);
        }

        .new-chat-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, rgba(0, 210, 255, 0.1), rgba(0, 210, 255, 0.05));
            border: 1px solid rgba(0, 210, 255, 0.4);
            border-radius: 16px;
            color: var(--primary-teal);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 13px;
        }

        .new-chat-btn:hover { 
            background: rgba(0, 210, 255, 0.2); 
            border-color: var(--primary-teal);
            box-shadow: 0 0 20px rgba(0, 210, 255, 0.2);
            transform: translateY(-2px);
        }

        .history-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }

        .folder-group {
            margin-bottom: 15px;
        }

        .folder-header {
            padding: 15px 10px 8px 10px;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 1.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .history-item {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 6px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            border: 1px solid transparent;
            background: rgba(255, 255, 255, 0.02);
        }

        .history-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-light);
            transform: translateX(5px);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .history-item.active {
            background: linear-gradient(90deg, rgba(0, 210, 255, 0.15), transparent);
            border-left: 3px solid var(--primary-teal);
            color: var(--primary-teal);
            border-radius: 8px 16px 16px 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .history-item i {
            font-size: 16px;
            opacity: 0.7;
        }
        
        .history-item.active i {
            color: var(--primary-teal);
            opacity: 1;
        }

        .chat-container {
            flex: 1;
            background: var(--glass-bg);
            backdrop-filter: blur(15px) saturate(150%);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.8),
                        inset 0 0 20px rgba(255, 255, 255, 0.02);
            overflow: hidden;
            position: relative;
            animation: containerFadeIn 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes containerFadeIn {
            from { opacity: 0; transform: translateY(15px) scale(0.99); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Header */
        .chat-header {
            padding: 24px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(0, 0, 0, 0.2);
        }

        .ai-avatar {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #00e5ff, #0077ff);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 26px;
            color: white;
            box-shadow: 0 0 20px rgba(0, 229, 255, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 229, 255, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(0, 229, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 229, 255, 0); }
        }

        .sidebar-toggle {
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            margin-right: 15px;
            transition: 0.3s;
        }

        .sidebar-toggle:hover { color: var(--primary-teal); }

        .header-title h1 {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-teal);
            margin-bottom: 0;
            font-family: 'Inter', sans-serif;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .model-selector {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-light);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            outline: none;
            cursor: pointer;
            transition: 0.3s;
        }
        .model-selector:hover { border-color: var(--primary-teal); }

        .usage-container {
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 100px;
        }
        .usage-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            overflow: hidden;
        }
        .usage-fill {
            height: 100%;
            background: var(--primary-teal);
            width: 30%;
            border-radius: 2px;
            box-shadow: 0 0 10px var(--primary-teal);
        }
        .usage-text { font-size: 9px; color: #64748b; text-align: right; }

        /* Chat Logs */
        .chat-logs {
            flex: 1;
            padding: 30px 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
            scroll-behavior: smooth;
        }

        /* Scrollbar Styling */
        .chat-logs::-webkit-scrollbar {
            width: 6px;
        }
        .chat-logs::-webkit-scrollbar-track {
            background: transparent;
        }
        .chat-logs::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .chat-logs::-webkit-scrollbar-thumb:hover {
            background: var(--primary-teal);
        }

        /* Messages */
        .message {
            max-width: 80%;
            padding: 18px 25px;
            border-radius: 20px;
            animation: slideIn 0.15s ease-out forwards;
            opacity: 0;
            transform: translateY(5px);
            font-size: 14px;
            word-wrap: break-word;
        }

        @keyframes slideIn {
            to { opacity: 1; transform: translateY(0); }
        }

        .user-message {
            align-self: flex-end;
            background: var(--message-user);
            border: 2px solid rgba(0, 229, 255, 0.4);
            border-top-right-radius: 4px;
            color: #00e5ff;
            box-shadow: 8px 8px 24px rgba(0, 0, 0, 0.4), 
                        inset 1px 1px 3px rgba(255, 255, 255, 0.15);
            border-bottom-width: 4px;
            border-right-width: 4px;
            transform: perspective(1000px) translateZ(0);
        }

        .ai-message {
            align-self: flex-start;
            background: var(--message-ai);
            border: 2px solid var(--glass-border);
            border-top-left-radius: 4px;
            color: #e9d5ff;
            box-shadow: -8px 8px 24px rgba(0, 0, 0, 0.5), 
                        inset 1px 1px 3px rgba(255, 255, 255, 0.1);
            border-bottom-width: 4px;
            border-left-width: 4px;
            transform: perspective(1000px) translateZ(0);
        }

        .ai-message * {
            font-family: 'Inter', 'Noto Nastaliq Urdu', sans-serif;
        }





        
        /* Markdown Styling inside AI Message */
        .ai-message p { margin-bottom: 10px; }
        .ai-message p:last-child { margin-bottom: 0; }
        .ai-message strong { color: var(--primary-teal); font-weight: bold; }
        
        /* Image Preview */
        .image-preview-container {
            display: none;
            padding: 10px 24px;
            background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid var(--glass-border);
            gap: 10px;
            overflow-x: auto;
        }
        .preview-wrapper {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--primary-teal);
        }
        .preview-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .remove-img {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(239, 68, 68, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .uploaded-message-img {
            max-width: 200px;
            border-radius: 10px;
            margin-top: 10px;
            display: block;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Copy Button */
        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: 0.3s;
        }
        .copy-btn:hover { background: var(--primary-teal); color: #000; }

        .ai-message pre {
            position: relative;
            background: #0f172a;
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
            margin-top: 15px;
            margin-bottom: 15px;
            border: 1px solid #334155;
        }

        /* Stop Button */
        .stop-btn {
            display: none;
            width: 45px;
            height: 45px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            margin-right: 10px;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            transition: 0.3s;
        }
        .stop-btn:hover { background: #dc2626; transform: scale(1.1); }

        /* Typing Indicator */
        .typing {
            display: none;
            align-self: flex-start;
            background: transparent;
            padding: 10px;
        }

        .typing span {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: var(--primary-teal);
            border-radius: 50%;
            margin-right: 5px;
            animation: bounce 0.8s infinite ease-in-out both;
        }

        .typing span:nth-child(1) { animation-delay: -0.32s; }
        .typing span:nth-child(2) { animation-delay: -0.16s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }

        /* Input Area */
        .chat-input-area {
            padding: 24px;
            background: rgba(0, 0, 0, 0.4);
            border-top: 1px solid var(--glass-border);
            display: flex;
            gap: 15px;
            align-items: center;
            position: relative;
        }

        .chat-input-area::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary-teal), transparent);
            opacity: 0.3;
        }

        .chat-input {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 16px 20px;
            color: #cbd5e1;
            font-family: 'Inter', 'Noto Nastaliq Urdu', sans-serif;
            font-size: 14px;
            outline: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            resize: none;
            max-height: 200px;
            min-height: 54px;
            overflow-y: auto;
        }

        .chat-input:focus {
            border-color: var(--primary-teal);
            box-shadow: 0 0 20px rgba(0, 210, 255, 0.15);
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-1px);
        }

        .send-btn, #mic-btn, #upload-btn {
            position: relative;
            overflow: visible;
        }

        .send-btn:hover, #mic-btn:hover, #upload-btn:hover {
            box-shadow: 0 0 25px var(--glow-color);
            transform: scale(1.1);
        }

        .send-btn {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--primary-teal), #0077ff);
            border: none;
            border-radius: 14px;
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 20px rgba(0, 210, 255, 0.2);
        }

        .send-btn:hover {
            transform: scale(1.1) rotate(-5deg);
            box-shadow: 0 15px 25px rgba(0, 210, 255, 0.3);
        }

        .send-btn:active {
            transform: scale(0.95);
        }

        /* Mic Active State */
        .mic-active {
            animation: pulse-red 1.5s infinite !important;
            background: linear-gradient(135deg, #ff4b2b, #ff416c) !important;
            color: white !important;
            box-shadow: 0 10px 20px rgba(255, 75, 43, 0.3) !important;
        }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); }
            70% { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        
        /* Live Talk Overlay */
        #live-talk-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(10, 15, 20, 0.95); z-index: 2000;
            display: none; flex-direction: column; align-items: center; justify-content: center;
            backdrop-filter: blur(15px);
        }
        .talk-avatar {
            width: 150px; height: 150px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-teal), var(--accent-purple));
            box-shadow: 0 0 50px rgba(34, 184, 201, 0.5);
            margin-bottom: 30px; display: flex; align-items: center; justify-content: center;
            font-size: 50px; color: white; animation: pulse 2s infinite;
        }
        .talk-status-msg { font-size: 1.2rem; color: #a5b4fc; margin-bottom: 40px; }
        .talk-controls { display: flex; gap: 20px; }
        .talk-btn {
            padding: 15px 30px; border-radius: 30px; border: none;
            background: rgba(255, 255, 255, 0.1); color: white;
            cursor: pointer; font-size: 1rem; transition: all 0.3s;
            display: flex; align-items: center; gap: 10px;
        }
        .talk-btn:hover { background: rgba(255, 255, 255, 0.2); transform: translateY(-3px); }
        .stop-talk { background: #ef4444 !important; }
        .test-talk { background: #10b981 !important; }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 30px rgba(34, 184, 201, 0.5); }
            50% { transform: scale(1.05); box-shadow: 0 0 60px rgba(34, 184, 201, 0.8); }
            100% { transform: scale(1); box-shadow: 0 0 30px rgba(34, 184, 201, 0.5); }
        }

        /* Themes */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .theme-selector {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        .theme-dot {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: 0.3s;
        }
        .theme-dot.active { border-color: white; transform: scale(1.1); }
        .theme-cyan { background: #22b8c9; }
        .theme-purple { background: #8b5cf6; }
        .theme-pink { background: #ec4899; }
        .theme-orange { background: #f97316; }

        @media (max-width: 768px) {
            .sidebar { position: fixed; height: 100%; z-index: 1001; }
            .chat-container { border-radius: 0; }
        }

        /* --- Suggestion Chips (Gemini Style) --- */
        .suggestion-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-top: 30px;
            padding: 0 40px;
            max-width: 800px;
            align-self: center;
        }

        .suggestion-chip {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(0, 0, 0, 0.05);
            color: #374151;
            padding: 12px 24px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .suggestion-chip:hover {
            background: #fdfdfd;
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-teal);
        }

        .suggestion-chip i { font-size: 18px; }

        /* --- Tools Menu Styling (Gemini Style) --- */
        .tools-menu {
            position: absolute;
            bottom: 85px;
            left: 20px;
            background: rgba(8, 12, 20, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            width: 310px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.05);
            padding: 12px;
            display: none;
            flex-direction: column;
            z-index: 1000;
            transform-origin: bottom left;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: menuPopup 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .tools-menu.active {
            display: flex;
        }

        @keyframes menuPopup {
            from { opacity: 0; transform: translateY(15px) scale(0.85); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .tools-menu-item {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            gap: 15px;
            color: #d1d5db;
            cursor: pointer;
            border-radius: 16px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 14.5px;
            font-weight: 500;
        }

        /* --- Language Switcer & Direction Logic --- */
        body.lang-en { direction: ltr; font-family: 'Inter', sans-serif; }
        body.lang-ur { direction: rtl; font-family: 'Noto Nastaliq Urdu', serif; }

        .lang-switcher {
            display: flex;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 4px;
            margin-right: 15px;
        }

        body.lang-ur .lang-switcher {
            margin-right: 0;
            margin-left: 15px;
        }

        .lang-btn {
            padding: 6px 14px;
            border-radius: 16px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            color: rgba(255, 255, 255, 0.6);
            border: none;
            background: transparent;
        }

        .lang-btn.active {
            background: white;
            color: #1f2937;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .lang-ur .sidebar { right: auto; left: auto; }
        .lang-ur .chat-container { border-radius: 24px 0 0 24px; }
        @media (max-width: 768px) {
            .lang-ur .sidebar { right: -100%; left: auto; transition: left 0.3s ease; }
            .lang-ur .sidebar.active { right: auto; left: auto; }
        }

        .urdu-text, body.lang-ur .message, body.lang-ur .chat-input {
            font-family: 'Noto Nastaliq Urdu', serif !important;
            font-size: 14px !important;
            line-height: 2.0 !important;
        }

        .tools-menu-item:hover {
            background: rgba(0, 210, 255, 0.1);
            color: var(--primary-teal);
            transform: scale(1.02) translateX(8px);
        }

        .tools-menu-item i {
            font-size: 18px;
            width: 24px;
            text-align: center;
            color: var(--primary-teal);
            filter: drop-shadow(0 0 5px rgba(0, 210, 255, 0.3));
        }

        .tools-menu-item span {
            flex: 1;
        }

        .tools-menu-item .more-arrow {
            font-size: 12px;
            color: #9ca3af;
            margin-right: -2px;
        }

        #tools-toggle-btn {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.08) !important;
            color: var(--primary-teal) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 18px !important;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
        }
        
        #tools-toggle-btn:hover {
            transform: scale(1.1) rotate(90deg);
            background: rgba(0, 210, 255, 0.15) !important;
            border-color: var(--primary-teal) !important;
            box-shadow: 0 0 20px rgba(0, 210, 255, 0.3) !important;
        }

        #tools-toggle-btn.active {
            transform: rotate(45deg);
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
        }
    </style>
</head>
<body>

    <canvas id="bg-canvas"></canvas>
    <div class="ambient-glow"></div>

    <div id="live-talk-overlay">
        <div class="talk-avatar"><i class="fa-solid fa-microphone-lines"></i></div>
        <div class="talk-status-msg" id="talk-status">Listening...</div>
        <div class="talk-controls">
            <button id="stop-live-talk" class="talk-btn stop-talk"><i class="fa-solid fa-phone-slash"></i> Stop Talk</button>
        </div>
    </div>

    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <button class="new-chat-btn" id="new-chat-btn-sidebar">
                    <i class="fa-solid fa-plus"></i>
                    New Chat
                </button>
            </div>
            <div class="history-list" id="history-list">
                <!-- History items load here -->
            </div>
            <div class="sidebar-footer">
                <p style="font-size: 12px; color: #64748b; text-align: center; margin-bottom: 5px;">Themes</p>
                <div class="theme-selector">
                    <div class="theme-dot theme-cyan active" data-theme="cyan"></div>
                    <div class="theme-dot theme-purple" data-theme="purple"></div>
                    <div class="theme-dot theme-pink" data-theme="pink"></div>
                    <div class="theme-dot theme-orange" data-theme="orange"></div>
                </div>
            </div>
        </div>

        <div class="chat-container">
            <!-- Header -->
            <div class="chat-header">
                <button class="sidebar-toggle" id="sidebar-toggle">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
                <div class="ai-avatar" style="width: 40px; height: 40px; font-size: 18px;">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div class="header-title" style="flex: 1;">
                    <h1>ZU AI</h1>
                </div>
                <div class="header-controls">
                    <div class="lang-switcher">
                        <button class="lang-btn" id="btn-en" onclick="setLanguage('en')">English</button>
                        <button class="lang-btn" id="btn-ur" onclick="setLanguage('ur')">&#x0627;&#x0631;&#x062F;&#x0648;</button>
                    </div>
                    <button id="live-talk-btn" title="Live Voice Talk" style="background: rgba(0, 229, 255, 0.1); color: var(--primary-teal); border: 1px solid rgba(0, 229, 255, 0.3); border-radius: 50%; width: 40px; height: 40px; cursor: pointer; display: flex; justify-content: center; align-items: center; transition: all 0.3s ease;">
                        <i class="fa-solid fa-phone"></i>
                    </button>
                    <button id="clear-chat-btn" title="Reset Chat" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 50%; width: 40px; height: 40px; cursor: pointer; display: flex; justify-content: center; align-items: center; transition: all 0.3s ease; margin-left: 10px;">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Logs -->
            <div class="chat-logs" id="chat-logs">
                <div class="typing" id="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            </div>

            <style>
                @keyframes containerFadeIn {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            </style>

            <!-- Image Preview Area -->
            <div id="image-preview" class="image-preview-container"></div>

            <!-- Input Area -->
            <div class="chat-input-area">
                <!-- Tools Menu -->
                <div class="tools-menu" id="tools-menu">
                    <div class="tools-menu-item" id="tool-upload">
                        <i class="fa-solid fa-paperclip"></i>
                        <span>Upload photos & files</span>
                    </div>
                    <div class="tools-menu-item" id="tool-image">
                        <i class="fa-regular fa-image"></i>
                        <span>Create image</span>
                    </div>
                    <div class="tools-menu-item" id="tool-thinking">
                        <i class="fa-regular fa-lightbulb"></i>
                        <span>Thinking</span>
                    </div>
                    <div class="tools-menu-item" id="tool-research">
                        <i class="fa-solid fa-binoculars"></i>
                        <span>Deep research</span>
                    </div>
                    <div class="tools-menu-item" id="tool-shopping">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span>Shopping research</span>
                    </div>
                    <div class="tools-menu-item" id="more-tools-btn" style="margin-top: 5px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 15px;">
                        <i class="fa-solid fa-ellipsis"></i>
                        <span>More</span>
                        <i class="fa-solid fa-chevron-right more-arrow" id="more-arrow" style="transition: transform 0.3s;"></i>
                    </div>
                    <!-- Secondary Tools (Hidden by default) -->
                    <div id="secondary-tools" style="display: none; flex-direction: column; overflow: hidden; transition: all 0.3s ease;">
                        <div class="tools-menu-item" id="tool-search">
                            <i class="fa-solid fa-globe"></i>
                            <span>Web search</span>
                        </div>
                        <div class="tools-menu-item" id="tool-study">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>Study and learn</span>
                        </div>
                        <div class="tools-menu-item" id="tool-canvas">
                            <i class="fa-solid fa-palette"></i>
                            <span>Canvas</span>
                        </div>
                        <div class="tools-menu-item" id="tool-quiz">
                            <i class="fa-solid fa-file-circle-check"></i>
                            <span>Quizzes</span>
                        </div>
                    </div>
                </div>

                <input type="file" id="file-input" style="display: none;" accept="image/*">
                <button id="tools-toggle-btn" title="Add Tools">
                    <i class="fa-solid fa-plus"></i>
                </button>
                <button id="stop-btn" class="stop-btn" title="Stop Generation">
                    <i class="fa-solid fa-stop"></i>
                </button>
                <button id="mic-btn" class="send-btn" title="Voice Typing" style="background: rgba(0, 229, 255, 0.1); color: var(--primary-teal); border: 1px solid rgba(0, 229, 255, 0.3);">
                    <i class="fa-solid fa-microphone"></i>
                </button>
                <textarea id="chat-input" class="chat-input" placeholder="Ask anything..." autocomplete="off" rows="1"></textarea>
                <button id="send-btn" class="send-btn">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const historyList = document.getElementById('history-list');
            const newChatBtnSidebar = document.getElementById('new-chat-btn-sidebar');
            const stopBtn = document.getElementById('stop-btn');
            
            const sendBtn = document.getElementById('send-btn');
            const micBtn = document.getElementById('mic-btn');
            const toolsToggleBtn = document.getElementById('tools-toggle-btn');
            const toolsMenu = document.getElementById('tools-menu');
            const toolUpload = document.getElementById('tool-upload');
            const fileInput = document.getElementById('file-input');
            const imagePreview = document.getElementById('image-preview');
            const liveTalkBtn = document.getElementById('live-talk-btn');
            const stopLiveTalkBtn = document.getElementById('stop-live-talk');
            const liveTalkOverlay = document.getElementById('live-talk-overlay');
            const talkStatus = document.getElementById('talk-status');
            const clearChatBtn = document.getElementById('clear-chat-btn');
            const chatInput = document.getElementById('chat-input');
            const chatLogs = document.getElementById('chat-logs');
            const typingIndicator = document.getElementById('typing-indicator');

            let sessions = [];
            let currentSessionId = null;
            let chatHistory = [];
            let isLiveTalk = false;
            let isSpeaking = false;
            let isProcessing = false;
            let abortController = null;
            let currentImageData = null; 
            let currentMode = 'chat';
            let selectedLang = localStorage.getItem('selectedLang') || 'en';
            
            const greetingEn = "Hello! I am ZU AI. How can I help you today?";
            const greetingUr = "\u0628\u062a\u0627\u0626\u064a\u06ba\u060c \u0622\u067e \u06a9\u0648 \u06a9\u0633 \u0686\u064a\u0632 \u0645\u064a\u06ba \u0645\u062f\u062f \u0686\u0627\u06c1\u064a\u06d2\u061f";
            let initialGreeting = (selectedLang === 'ur') ? greetingUr : greetingEn;

            window.setLanguage = function(lang) {
                selectedLang = lang;
                localStorage.setItem('selectedLang', lang);
                document.body.className = `lang-${lang}`;
                
                const btnEn = document.getElementById('btn-en');
                const btnUr = document.getElementById('btn-ur');
                if (btnEn) btnEn.classList.toggle('active', lang === 'en');
                if (btnUr) btnUr.classList.toggle('active', lang === 'ur');

                initialGreeting = (lang === 'ur') ? greetingUr : greetingEn;
                
                if (chatHistory.length === 0) {
                    const welcomeMsg = document.querySelector('.message.ai-message');
                    if (welcomeMsg) welcomeMsg.textContent = initialGreeting;
                }
                
                if (chatInput) chatInput.placeholder = (lang === 'ur') ? "\u06a9\u0686\u06be \u0628\u06be\u064a \u067e\u0648\u0686\u06be\u064a\u06ba..." : "Ask anything...";
            };

            // Initialize language
            window.setLanguage(selectedLang);

            // --- Premium Abstract 3D Wave Background ---
            function initBackground() {
                const canvas = document.getElementById('bg-canvas');
                if (!canvas) return;
                
                try {
                const scene = new THREE.Scene();
                const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
                const renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
                renderer.setSize(window.innerWidth, window.innerHeight);
                renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

                // Mesh Plane
                const geometry = new THREE.PlaneGeometry(35, 35, 80, 80);
                const material = new THREE.MeshPhongMaterial({
                    color: 0x00d2ff,
                    wireframe: true,
                    transparent: true,
                    opacity: 0.15,
                    side: THREE.DoubleSide
                });
                
                const wave = new THREE.Mesh(geometry, material);
                wave.rotation.x = -Math.PI / 2.2;
                wave.position.y = -3;
                scene.add(wave);

                // Add points correctly for BufferGeometry
                const pointsMaterial = new THREE.PointsMaterial({
                    color: 0x00d2ff,
                    size: 0.03,
                    transparent: true,
                    opacity: 0.4
                });
                const points = new THREE.Points(geometry, pointsMaterial);
                points.rotation.x = -Math.PI / 2.2;
                points.position.y = -3;
                scene.add(points);

                // Lights
                const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
                scene.add(ambientLight);

                const pointLight = new THREE.PointLight(0x00d2ff, 1.5);
                pointLight.position.set(5, 5, 5);
                scene.add(pointLight);

                camera.position.z = 10;
                camera.position.y = 3;

                let count = 0;
                function animate() {
                    requestAnimationFrame(animate);
                    count += 0.01;

                    const positions = geometry.attributes.position.array;
                    for (let i = 0; i < positions.length; i += 3) {
                        const x = positions[i];
                        const y = positions[i + 1];
                        positions[i + 2] = 
                            Math.sin(x * 0.2 + count) * 1.2 +
                            Math.sin(y * 0.3 + count * 0.8) * 0.8;
                    }
                    geometry.attributes.position.needsUpdate = true;
                    
                    wave.rotation.z += 0.0005;
                    points.rotation.z += 0.0005;
                    
                    renderer.render(scene, camera);
                }

                window.addEventListener('resize', () => {
                    camera.aspect = window.innerWidth / window.innerHeight;
                    camera.updateProjectionMatrix();
                    renderer.setSize(window.innerWidth, window.innerHeight);
                });

                animate();
                } catch(e) { console.error("Three.js Error:", e); }
            }

            // --- Session Management ---
            function loadSessions() {
                const saved = localStorage.getItem('zu_ai_sessions');
                if (saved) {
                    try {
                        sessions = JSON.parse(saved);
                        currentSessionId = localStorage.getItem('zu_ai_current_id');
                        renderSidebar();
                        if (currentSessionId && sessions.find(s => s.id === currentSessionId)) {
                            switchSession(currentSessionId);
                        } else {
                            startNewChat();
                        }
                    } catch(e) { startNewChat(); }
                } else {
                    startNewChat();
                }
            }

            function saveSessions() {
                localStorage.setItem('zu_ai_sessions', JSON.stringify(sessions));
                localStorage.setItem('zu_ai_current_id', currentSessionId);
            }

            function renderSidebar() {
                historyList.innerHTML = '';
                const categories = ["Coding", "Writing", "Personal", "Recent"];
                
                categories.forEach(cat => {
                    const group = document.createElement('div');
                    group.className = 'folder-group';
                    group.innerHTML = `<div class="folder-header"><i class="fa-solid fa-folder-open"></i> ${cat}</div>`;
                    
                    const catSessions = sessions.filter(s => (s.category || "Recent") === cat);
                    catSessions.slice().reverse().forEach(session => {
                        const div = document.createElement('div');
                        div.className = `history-item ${session.id === currentSessionId ? 'active' : ''}`;
                        div.innerHTML = `<i class="fa-regular fa-message"></i> ${session.title}`;
                        div.onclick = () => switchSession(session.id);
                        group.appendChild(div);
                    });
                    
                    if (catSessions.length > 0) historyList.appendChild(group);
                });
            }

            function showSuggestionChips() {
                const container = document.createElement('div');
                container.className = 'suggestion-container';
                container.id = 'welcome-suggestions';
                
                const chips = [
                    { icon: '🖼️', text: 'Create image', mode: 'create_image' },
                    { icon: '🎸', text: 'Create music', mode: 'create_music' },
                    { icon: '☀️', text: 'Boost my day', mode: 'boost_day' },
                    { icon: '🎓', text: 'Help me learn', mode: 'study' },
                    { icon: '✍️', text: 'Write anything', mode: 'write_anything' },
                    { icon: '🎥', text: 'Create a video', mode: 'create_video' }
                ];

                chips.forEach(c => {
                    const chip = document.createElement('div');
                    chip.className = 'suggestion-chip';
                    chip.innerHTML = `<span>${c.icon}</span> ${c.text}`;
                    chip.onclick = () => {
                        currentMode = c.mode;
                        chatInput.value = c.text;
                        sendMessage();
                        container.remove();
                    };
                    container.appendChild(chip);
                });
                chatLogs.insertBefore(container, typingIndicator);
            }

            function startNewChat() {
                const id = Date.now().toString();
                const newSession = { id: id, title: "New Chat", history: [], category: "Recent" };
                sessions.push(newSession);
                currentSessionId = id;
                chatHistory = [];
                clearUI();
                appendMessage(initialGreeting, 'ai-message', false);
                showSuggestionChips();
                saveSessions();
                renderSidebar();
            }

            function switchSession(id) {
                const session = sessions.find(s => s.id === id);
                if (!session) return;
                currentSessionId = id;
                chatHistory = session.history;
                clearUI();
                if (chatHistory.length === 0) {
                    appendMessage(initialGreeting, 'ai-message', false);
                } else {
                    chatHistory.forEach(msg => {
                        const type = msg.role === 'user' ? 'user-message' : 'ai-message';
                        let text = "";
                        let imageData = null;
                        msg.parts.forEach(p => {
                            if (p.text) text = p.text;
                            if (p.inline_data) imageData = p.inline_data;
                        });
                        appendMessage(text, type, false, imageData);
                    });
                }
                renderSidebar();
                saveSessions();
            }

            function clearUI() {
                const msgs = document.querySelectorAll('.message');
                msgs.forEach(m => m.remove());
            }

            sidebarToggle.onclick = () => sidebar.classList.toggle('hidden');
            newChatBtnSidebar.onclick = startNewChat;

            stopBtn.onclick = () => {
                if (abortController) {
                    abortController.abort();
                    typingIndicator.style.display = 'none';
                    stopBtn.style.display = 'none';
                }
            };

            chatLogs.addEventListener('click', (e) => {
                const btn = e.target.closest('.copy-btn');
                if (btn) {
                    const targetId = btn.getAttribute('data-target');
                    const codeBlock = document.getElementById(targetId);
                    if (codeBlock) {
                        navigator.clipboard.writeText(codeBlock.innerText).then(() => {
                            const originalHTML = btn.innerHTML;
                            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
                            setTimeout(() => btn.innerHTML = originalHTML, 2000);
                        });
                    }
                }
            });

            clearChatBtn.addEventListener('click', () => {
                if(confirm("Reset current session history?")) {
                    const session = sessions.find(s => s.id === currentSessionId);
                    if (session) {
                        session.history = [];
                        chatHistory = [];
                        clearUI();
                        window.speechSynthesis.cancel();
                        appendMessage(initialGreeting, 'ai-message', false);
                        saveSessions();
                    }
                }
            });

            // Speech Recognition
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            let recognition;
            if (SpeechRecognition) {
                recognition = new SpeechRecognition();
                recognition.continuous = false; 
                recognition.interimResults = false;
                // Default language based on initial greeting (which is Urdu)
                const isUrduInitial = /[\u0600-\u06FF]/.test(initialGreeting);
                recognition.lang = isUrduInitial ? 'ur-PK' : 'en-US'; 

                recognition.onstart = () => {
                    micBtn.classList.add('mic-active');
                    const isUrdu = recognition.lang === 'ur-PK';
                    chatInput.placeholder = isUrdu ? "میں سن رہا ہوں..." : "Listening...";
                    if (isLiveTalk) {
                        talkStatus.textContent = isUrdu ? "ZU AI سن رہا ہے..." : "ZU AI is listening...";
                    }
                };
                recognition.onresult = (event) => {
                    const transcript = event.results[event.results.length - 1][0].transcript;
                    if (transcript.trim()) {
                        chatInput.value = (chatInput.value.trim() + " " + transcript).trim();
                        chatInput.dispatchEvent(new Event('input'));
                        if (isLiveTalk && !isProcessing) {
                             sendMessage();
                        }
                    }
                };
                recognition.onend = () => {
                    micBtn.classList.remove('mic-active');
                    chatInput.placeholder = "Ask anything...";
                    if (isLiveTalk && !isSpeaking && !isProcessing) {
                        setTimeout(() => {
                            if (isLiveTalk && !isSpeaking) startRecognition();
                        }, 500);
                    }
                };

                const startRecognition = () => {
                    const content = chatInput.value + (chatHistory.length > 0 ? JSON.stringify(chatHistory[chatHistory.length-1]) : "");
                    if (/[\u0600-\u06FF]/.test(content + initialGreeting)) {
                        recognition.lang = 'ur-PK';
                    } else {
                        recognition.lang = 'en-US';
                    }
                    try { recognition.start(); } catch(e) {}
                };

                micBtn.onclick = () => {
                    if (micBtn.classList.contains('mic-active')) recognition.stop();
                    else startRecognition();
                };
                
                liveTalkBtn.onclick = () => { 
                    isLiveTalk = true; 
                    liveTalkOverlay.style.display = 'flex'; 
                    startRecognition();
                };

                stopLiveTalkBtn.onclick = () => { 
                    isLiveTalk = false; 
                    liveTalkOverlay.style.display = 'none'; 
                    try { recognition.stop(); } catch(e) {}
                    window.speechSynthesis.cancel(); 
                };

                // The continuous listener logic is already handled in onend
            } else {
                micBtn.style.display = 'none'; liveTalkBtn.style.display = 'none';
            }

            // Prime on first interaction
            document.body.addEventListener('click', () => {
                if (window.speechSynthesis) {
                    window.speechSynthesis.getVoices();
                    const silent = new SpeechSynthesisUtterance("");
                    silent.volume = 0;
                    window.speechSynthesis.speak(silent);
                }
            }, { once: true });

            if (window.speechSynthesis && window.speechSynthesis.onvoiceschanged !== undefined) {
                window.speechSynthesis.onvoiceschanged = () => window.speechSynthesis.getVoices();
            }

            function stripMarkdown(text) {
                return text
                    .replace(/#{1,6}\s?/g, '') 
                    .replace(/\*\*?\/?/g, '') 
                    .replace(/```[\s\S]*?```/g, 'Code block omitted.') 
                    .replace(/`([^`]+)`/g, '$1') 
                    .replace(/\[([^\]]+)\]\([^\)]+\)/g, '$1') 
                    .replace(/[\*\-]\s/g, '') 
                    .trim();
            }

            function speakText(text) {
                if (!window.speechSynthesis) return;
                
                isSpeaking = true;
                window.speechSynthesis.cancel();
                
                if (window.speechSynthesis.paused) {
                    window.speechSynthesis.resume();
                }

                const cleanText = stripMarkdown(text);
                if (!cleanText) { 
                    isSpeaking = false; 
                    if (isLiveTalk) {
                        talkStatus.textContent = "Listening...";
                        try { recognition.start(); } catch(e) {}
                    }
                    return; 
                }

                const utterance = new SpeechSynthesisUtterance(cleanText);
                window._currentUtterance = utterance; 
                
                const voices = window.speechSynthesis.getVoices();
                const isUrduValue = /[\u0600-\u06FF]/.test(text);
                
                let selectedVoice = voices.find(v => {
                    const name = v.name.toLowerCase();
                    const femaleKey = ['female', 'zira', 'samantha', 'google uk english female', 'microsoft sabina', 'leila', 'sashimi'].some(k => name.includes(k));
                    const langMatch = isUrduValue ? (v.lang.includes('ur') || v.lang.includes('ar')) : v.lang.startsWith('en');
                    return femaleKey && langMatch;
                });

                if (!selectedVoice) {
                    selectedVoice = voices.find(v => isUrduValue ? (v.lang.includes('ur') || v.lang.includes('ar')) : v.lang.startsWith('en'));
                }

                if (selectedVoice) {
                    utterance.voice = selectedVoice;
                } else {
                    utterance.lang = isUrduValue ? 'ur-PK' : 'en-US';
                }

                // Update recognition language for next input
                if (recognition) {
                    recognition.lang = isUrduValue ? 'ur-PK' : 'en-US';
                    console.log("Recognition lang updated to:", recognition.lang);
                }

                utterance.volume = 1.0;
                utterance.pitch = 1.1;
                utterance.rate = 1.0;

                utterance.onstart = () => {
                    if (isLiveTalk) {
                        const isUrdu = /[\u0600-\u06FF]/.test(text);
                        talkStatus.textContent = isUrdu ? "ZU AI جواب دے رہا ہے..." : "ZU AI is speaking...";
                        if (recognition) try { recognition.stop(); } catch(e) {}
                    }
                };

                utterance.onend = () => {
                    isSpeaking = false;
                    if (isLiveTalk) {
                        const isUrdu = /[\u0600-\u06FF]/.test(text);
                        talkStatus.textContent = isUrdu ? "میں آپ کو سن رہا ہوں..." : "Listening...";
                        setTimeout(() => {
                            if (isLiveTalk && !isSpeaking && !isProcessing) {
                                startRecognition();
                            }
                        }, 800);
                    }
                };

                utterance.onerror = (e) => {
                    console.error("Speech Synthesis error:", e);
                    isSpeaking = false;
                    if (isLiveTalk) {
                        talkStatus.textContent = "Listening...";
                        try { recognition.start(); } catch(err) {}
                    }
                };

                setTimeout(() => {
                    window.speechSynthesis.speak(utterance);
                }, 100);
            }

            // Code Copying & Markdown
            const renderer = new marked.Renderer();
            renderer.code = function(code, language) {
                const id = 'copy-btn-' + Math.random().toString(36).substr(2, 9);
                return `<div style="position: relative;"><button class="copy-btn" data-target="${id}"><i class="fa-solid fa-copy"></i> Copy</button><pre id="${id}"><code>${code}</code></pre></div>`;
            };
            marked.setOptions({ renderer: renderer, breaks: true, gfm: true });

            document.querySelectorAll('.theme-dot').forEach(dot => {
                dot.onclick = () => {
                    document.querySelectorAll('.theme-dot').forEach(d => d.classList.remove('active'));
                    dot.classList.add('active');
                    const colorMap = { 
                        cyan: { primary: '#00d2ff', rgb: '0, 210, 255' }, 
                        purple: { primary: '#a855f7', rgb: '168, 85, 247' }, 
                        pink: { primary: '#ec4899', rgb: '236, 72, 153' }, 
                        orange: { primary: '#f97316', rgb: '249, 115, 22' } 
                    };
                    const choice = colorMap[dot.dataset.theme];
                    document.documentElement.style.setProperty('--primary-teal', choice.primary);
                    document.documentElement.style.setProperty('--primary-teal-rgb', choice.rgb);
                    document.documentElement.style.setProperty('--glow-color', `rgba(${choice.rgb}, 0.4)`);
                };
            });

            // Auto-expand textarea and detect language
            chatInput.addEventListener('input', () => {
                chatInput.style.height = 'auto';
                chatInput.style.height = (chatInput.scrollHeight) + 'px';
                
                // Detect Urdu for RTL and Typography
                if (/[\u0600-\u06FF]/.test(chatInput.value)) {
                    chatInput.classList.add('urdu-text');
                    chatInput.placeholder = "اردو میں پوچھیں...";
                    if (recognition && recognition.lang !== 'ur-PK') {
                        recognition.lang = 'ur-PK';
                    }
                } else {
                    chatInput.classList.remove('urdu-text');
                    chatInput.placeholder = "Ask anything...";
                    if (recognition && recognition.lang !== 'en-US') {
                        recognition.lang = 'en-US';
                    }
                }
            });

            // Tools Menu Toggle
            toolsToggleBtn.onclick = (e) => {
                e.stopPropagation();
                toolsMenu.classList.toggle('active');
                toolsToggleBtn.classList.toggle('active');
            };

            // Close menu on click outside
            document.addEventListener('click', (e) => {
                if (!toolsMenu.contains(e.target) && e.target !== toolsToggleBtn) {
                    toolsMenu.classList.remove('active');
                    toolsToggleBtn.classList.remove('active');
                }
            });

            toolUpload.onclick = () => {
                fileInput.click();
                toolsMenu.classList.remove('active');
                toolsToggleBtn.classList.remove('active');
            };

            // Tools Functional Integration
            const toolConfigs = {
                'tool-image': { mode: 'create_image', placeholder: 'Describe the image you want ZU AI to visualize...', color: '#a855f7' },
                'tool-thinking': { mode: 'thinking', placeholder: 'Ask a complex question to think deeply about...', color: '#3b82f6' },
                'tool-research': { mode: 'research', placeholder: 'Enter a topic for deep research report...', color: '#10b981' },
                'tool-shopping': { mode: 'shopping', placeholder: 'What product are you looking for?', color: '#f59e0b' },
                'tool-search': { mode: 'web_search', placeholder: 'What do you want to search on the web?', color: '#00d2ff' },
                'tool-study': { mode: 'study', placeholder: 'What topic do you want to study today?', color: '#6366f1' },
                'tool-canvas': { mode: 'canvas', placeholder: 'Describe the design or code you want to build...', color: '#ec4899' },
                'tool-quiz': { mode: 'quiz', placeholder: 'Enter a topic to start a quiz...', color: '#ef4444' },
                'tool-music': { mode: 'create_music', placeholder: 'Describe the music you want to create...', color: '#f43f5e' },
                'tool-video': { mode: 'create_video', placeholder: 'Describe the video scene you want ZU AI to direct...', color: '#fbbf24' },
                'tool-boost': { mode: 'boost_day', placeholder: 'Need a boost? Ask ZU AI for motivation...', color: '#10b981' }
            };

            Object.keys(toolConfigs).forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.onclick = (e) => {
                        e.stopPropagation();
                        const config = toolConfigs[id];
                        currentMode = config.mode;
                        chatInput.placeholder = config.placeholder;
                        chatInput.style.borderColor = config.color;
                        chatInput.focus();
                        
                        toolsMenu.classList.remove('active');
                        toolsToggleBtn.classList.remove('active');
                        
                        // Small toast notification
                        const modeEmoji = el.querySelector('i').className;
                        appendMessage(`Mode switched: **${el.querySelector('span').textContent}** active.`, 'ai-message');
                    };
                }
            });

            // More Options Toggle (Web search, study, canvas, quizzes)
            const moreToolsBtn = document.getElementById('more-tools-btn');
            const secondaryTools = document.getElementById('secondary-tools');
            const moreArrow = document.getElementById('more-arrow');

            moreToolsBtn.onclick = (e) => {
                e.stopPropagation();
                const isHidden = secondaryTools.style.display === 'none';
                secondaryTools.style.display = isHidden ? 'flex' : 'none';
                moreArrow.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
                
                // Adjust menu height automatically
                if (isHidden) {
                    toolsMenu.style.maxHeight = "500px";
                }
            };
            fileInput.onchange = (e) => {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    currentImageData = { mime_type: file.type, data: ev.target.result.split(',')[1] };
                    imagePreview.style.display = 'flex';
                    imagePreview.innerHTML = `<div class="preview-wrapper"><img src="${ev.target.result}"><button class="remove-img" id="remove-img-btn"><i class="fa-solid fa-xmark"></i></button></div>`;
                    document.getElementById('remove-img-btn').onclick = () => { currentImageData = null; imagePreview.style.display = 'none'; fileInput.value = ''; };
                };
                reader.readAsDataURL(file);
            };

            function appendMessage(text, type, shouldScroll = true, imageData = null) {
                const msgDiv = document.createElement('div');
                msgDiv.className = `message ${type}`;
                if (type === 'ai-message') {
                    msgDiv.innerHTML = marked.parse(text);
                    if (/[\u0600-\u06FF]/.test(text)) {
                        msgDiv.classList.add('urdu-text');
                    }
                } else {
                    msgDiv.textContent = text;
                    if (/[\u0600-\u06FF]/.test(text)) {
                        msgDiv.classList.add('urdu-text');
                    }
                    if (imageData) {
                        const img = document.createElement('img');
                        img.src = `data:${imageData.mime_type};base64,${imageData.data}`;
                        img.className = 'uploaded-message-img';
                        msgDiv.appendChild(img);
                    }
                }
                chatLogs.insertBefore(msgDiv, typingIndicator);
                if (shouldScroll) chatLogs.scrollTop = chatLogs.scrollHeight;
            }

            async function sendMessage() {
                if (isProcessing) return;
                const text = chatInput.value.trim();
                if (!text && !currentImageData) return;
                
                isProcessing = true;
                appendMessage(text, 'user-message', true, currentImageData);
                chatInput.value = '';
                
                // If in live talk, stop recognition immediately while processing/sending
                if (isLiveTalk && recognition) {
                    try { recognition.stop(); } catch(e) {}
                }

                let userParts = [];
                if (text) userParts.push({ text: text });
                if (currentImageData) userParts.push({ inline_data: currentImageData });
                currentImageData = null; imagePreview.style.display = 'none'; fileInput.value = '';
                typingIndicator.style.display = 'block';
                stopBtn.style.display = 'flex';
                chatLogs.scrollTop = chatLogs.scrollHeight;
                chatHistory.push({ role: "user", parts: userParts });
                
                const session = sessions.find(s => s.id === currentSessionId);
                if (session && session.title === "New Chat") { 
                    session.title = text.substring(0, 25) + (text.length > 25 ? '...' : ''); 
                    // Simple logic to assign folder based on content
                    if (text.toLowerCase().includes('code') || text.toLowerCase().includes('debug')) session.category = "Coding";
                    else if (text.length > 50) session.category = "Writing";
                    else session.category = "Personal";
                    renderSidebar(); 
                }
                
                
                if (isLiveTalk) {
                    talkStatus.textContent = "ZU AI is thinking...";
                }

                abortController = new AbortController();
                try {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            history: chatHistory, 
                            mode: currentMode,
                            selectedLang: selectedLang
                        }),
                        signal: abortController.signal
                    });
                    const result = await response.json();
                    typingIndicator.style.display = 'none'; stopBtn.style.display = 'none';
                    if (result.status === 'success') {
                        appendMessage(result.reply, 'ai-message');
                        if (isLiveTalk) {
                            speakText(result.reply);
                        }
                        chatHistory.push({ role: "model", parts: [{text: result.reply}] });
                        saveSessions();
                    } else {
                        if (isLiveTalk) talkStatus.textContent = result.message || "Error occurred.";
                        appendMessage('Error: ' + result.message, 'ai-message');
                        
                        // Handle Quota Limit (429) UI state
                        if (result.message && result.message.includes('Quota')) {
                            let secondsLeft = 60;
                            const originalPlaceholder = chatInput.placeholder;
                            const originalSendHTML = sendBtn.innerHTML;
                            
                            sendBtn.disabled = true;
                            micBtn.disabled = true;
                            chatInput.disabled = true;
                            
                            const timer = setInterval(() => {
                                secondsLeft--;
                                chatInput.placeholder = `Quota limit reached. Wait ${secondsLeft}s...`;
                                if (secondsLeft <= 0) {
                                    clearInterval(timer);
                                    sendBtn.disabled = false;
                                    micBtn.disabled = false;
                                    chatInput.disabled = false;
                                    chatInput.placeholder = originalPlaceholder;
                                    sendBtn.innerHTML = originalSendHTML;
                                }
                            }, 1000);
                        }
                        
                        chatHistory.pop();
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') { 
                        if (isLiveTalk) talkStatus.textContent = "Connection failed.";
                        appendMessage('Connection error.', 'ai-message'); chatHistory.pop(); 
                    }
                } finally {
                    isProcessing = false;
                    typingIndicator.style.display = 'none'; 
                    stopBtn.style.display = 'none';
                }
            }

            chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });
            sendBtn.onclick = sendMessage;
            loadSessions();
            initBackground();
            chatInput.focus();
        });
    </script>
</body>
</html>
